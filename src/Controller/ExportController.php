<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\Annonce\Annonce;
use App\Model\Annonce\AnnonceRepository;
use App\Model\Annonce\EtatAnnonce;
use App\Model\Bien\Bien;
use App\Model\Bien\BienRepository;
use App\Model\Bien\CategorieBien;
use App\Model\Bien\TypeBien;
use App\Model\Bien\Ville;
use App\Model\Exporter\AnnonceArrayConverter;
use App\Model\Exporter\BienArrayConverter;
use App\Model\Form\DecimalParser;
use App\Model\Form\ExportFilterExtractor;
use App\View\ViewRenderer;

/* *
 * Contrôleur pour la gestion de l'export des annonces et biens immobiliers.
 */
final class ExportController
{
    private string $currentAction = 'export';

    public function __construct(
        private readonly AnnonceRepository $annonceRepository,
        private readonly BienRepository $bienRepository,
        private readonly ViewRenderer $views,
        private readonly array $annonceExporters,
        private readonly array $bienExporters,
        private readonly ExportFilterExtractor $filterExtractor,
        private readonly DecimalParser $decimals,
    ) {
    }

    public function dispatch(Request $request): Response
    {
        $legacyFormat = $request->query('export');
        if ($legacyFormat !== null) {
            return $this->download('annonces', $legacyFormat, $this->emptyAnnonceFilters(), $this->emptyBienFilters());
        }

        $target = $this->target($request);
        $annonceFilters = $target === 'annonces' ? $this->extractAnnonceFilters($request) : $this->emptyAnnonceFilters();
        $bienFilters = $target === 'biens' ? $this->extractBienFilters($request) : $this->emptyBienFilters();

        if ($request->query('download') === '1') {
            return $this->download(
                $target,
                $request->query('format', 'json') ?? 'json',
                $annonceFilters,
                $bienFilters,
            );
        }

        $annonces = $this->annonceRepository->findAll();
        $biens = $this->bienRepository->findUnlinked();

        return new Response($this->views->render('export/index', [
            'title'              => 'Export',
            'currentAction'      => $this->currentAction,
            'annonceFilters'     => $annonceFilters,
            'bienFilters'        => $bienFilters,
            'annoncesTotal'      => count($this->filterAnnonces($annonceFilters)),
            'biensTotal'         => count($this->filterBiens($biens, $bienFilters)),
            'annonceDataset'     => array_map(
                static fn(Annonce $annonce): array => AnnonceArrayConverter::toArray($annonce),
                $annonces,
            ),
            'bienDataset'        => array_map(
                static fn(Bien $bien): array => BienArrayConverter::toArray($bien),
                $biens,
            ),
            'annonceExporters'   => $this->annonceExporters,
            'bienExporters'      => $this->bienExporters,
            'categorieOptions'   => $this->categorieOptions(),
            'typeOptions'        => $this->typeOptions(),
            'transactionOptions' => $this->transactionOptions(),
            'etatOptions'        => $this->etatOptions(),
            'villeOptions'       => $this->villeOptions(),
        ]));
    }

    private function download(string $target, string $format, array $annonceFilters, array $bienFilters): Response
    {
        $exporters = $target === 'biens' ? $this->bienExporters : $this->annonceExporters;
        if (!isset($exporters[$format])) {
            return new Response($this->views->render('errors/404', [
                'title' => 'Page introuvable',
                'currentAction' => $this->currentAction,
            ]), 404);
        }

        $items = $target === 'biens'
            ? $this->filterBiens($this->bienRepository->findUnlinked(), $bienFilters)
            : $this->filterAnnonces($annonceFilters);

        $exporter = $exporters[$format];

        return new Response(
            $exporter->export($items),
            200,
            [
                'Content-Type'        => $exporter->getContentType(),
                'Content-Disposition' => 'attachment; filename="' . $exporter->getFilename() . '"',
            ],
        );
    }

    private function target(Request $request): string
    {
        return $request->query('target') === 'biens' ? 'biens' : 'annonces';
    }

    private function extractAnnonceFilters(Request $request): array
    {
        return $this->filterExtractor->extract(
            $request->queryArray('categorie'),
            $request->queryArray('type'),
            $request->queryArray('transaction'),
            $request->queryArray('etat'),
            $request->queryArray('ville'),
            $request->query('prix_max'),
        );
    }

    private function emptyAnnonceFilters(): array
    {
        return [
            'categorie'   => null,
            'type'        => null,
            'transaction' => null,
            'etat'        => null,
            'ville'       => null,
            'prixMax'     => null,
        ];
    }

    private function extractBienFilters(Request $request): array
    {
        return [
            'categorie'  => $this->validValues($request->queryArray('categorie'), $this->categorieValues()),
            'type'       => $this->validValues($request->queryArray('type'), $this->typeValues()),
            'ville'      => $this->validValues($request->queryArray('ville'), $this->villeValues()),
            'surfaceMax' => $this->parseOptionalDecimal($request->query('surface_max')),
        ];
    }

    private function emptyBienFilters(): array
    {
        return [
            'categorie'  => null,
            'type'       => null,
            'ville'      => null,
            'surfaceMax' => null,
        ];
    }

    private function filterAnnonces(array $filters): array
    {
        return $this->hasFilters($filters)
            ? $this->annonceRepository->findByFilters($filters)
            : $this->annonceRepository->findAll();
    }

    /**
     * @param Bien[] $biens
     * @return Bien[]
     */
    private function filterBiens(array $biens, array $filters): array
    {
        return array_values(array_filter($biens, function (Bien $bien) use ($filters): bool {
            if ($filters['categorie'] !== null && !in_array($bien->getCategorie()->value, $filters['categorie'], true)) {
                return false;
            }
            if ($filters['type'] !== null && !in_array((string) $bien->getType()?->value, $filters['type'], true)) {
                return false;
            }
            if ($filters['ville'] !== null && !in_array($bien->getVille(), $filters['ville'], true)) {
                return false;
            }
            if ($filters['surfaceMax'] !== null && bccomp((string) $bien->getSurface(), $filters['surfaceMax'], 2) > 0) {
                return false;
            }

            return true;
        }));
    }

    private function hasFilters(array $filters): bool
    {
        return array_filter($filters, static fn($value): bool => $value !== null && $value !== '') !== [];
    }

    /**
     * @param string[] $values
     * @param string[] $valides
     * @return string[]|null
     */
    private function validValues(array $values, array $valides): ?array
    {
        $values = array_values(array_unique(array_filter(
            array_map(static fn($value): string => (string) $value, $values),
            static fn(string $value): bool => in_array($value, $valides, true),
        )));

        return $values !== [] ? $values : null;
    }

    private function parseOptionalDecimal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace([' ', ','], ['', '.'], $value);
        if ($value === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            return null;
        }

        return $this->decimals->format($value);
    }

    private function categorieOptions(): array
    {
        $options = [];
        foreach (CategorieBien::cases() as $categorie) {
            $options[$categorie->value] = $categorie->libelle();
        }

        return $options;
    }

    private function typeOptions(): array
    {
        $options = [];
        foreach (TypeBien::cases() as $type) {
            $options[$type->value] = $type->libelle();
        }

        return $options;
    }

    private function transactionOptions(): array
    {
        return [
            'vente'    => 'Vente',
            'location' => 'Location',
        ];
    }

    private function etatOptions(): array
    {
        $options = [];
        foreach (EtatAnnonce::cases() as $etat) {
            $options[$etat->value] = $etat->getLibelle();
        }

        return $options;
    }

    private function villeOptions(): array
    {
        $options = [];
        foreach (Ville::cases() as $ville) {
            $options[$ville->value] = $ville->libelle();
        }

        return $options;
    }

    /**
     * @return string[]
     */
    private function categorieValues(): array
    {
        return array_map(static fn(CategorieBien $categorie): string => $categorie->value, CategorieBien::cases());
    }

    /**
     * @return string[]
     */
    private function typeValues(): array
    {
        return array_map(static fn(TypeBien $type): string => $type->value, TypeBien::cases());
    }

    /**
     * @return string[]
     */
    private function villeValues(): array
    {
        return array_map(static fn(Ville $ville): string => $ville->value, Ville::cases());
    }
}
