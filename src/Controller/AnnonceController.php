<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\Annonce\Annonce;
use App\Model\Annonce\AnnonceLocation;
use App\Model\Annonce\AnnonceRepository;
use App\Model\Annonce\AnnonceVente;
use App\Model\Annonce\EtatAnnonce;
use App\Model\Bien\BienRepository;
use App\Model\Bien\CategorieBien;
use App\Model\Bien\TypeBien;
use App\Model\Bien\Ville;
use App\Model\Form\AnnonceFormMapper;
use App\Model\Form\AnnonceFormValidator;
use App\Model\Service\EtatService;
use App\Model\Service\LoyerService;
use App\Model\Service\PrixService;
use App\View\AnnoncePresenter;
use App\View\CataloguePresenter;
use App\View\ViewRenderer;
use RuntimeException;

/* *
 * Contrôleur pour la gestion des annonces immobilières.
 */
final class AnnonceController
{
    private const ACTION_INDEX = '?action=index';

    private string $currentAction = 'index';
    private bool $fragment = false;

    public function __construct(
        private readonly AnnonceRepository $repository,
        private readonly BienRepository $bienRepository,
        private readonly AnnoncePresenter $annoncePresenter,
        private readonly CataloguePresenter $cataloguePresenter,
        private readonly ViewRenderer $views,
        private readonly PrixService $prixService,
        private readonly LoyerService $loyerService,
        private readonly EtatService $etatService,
        private readonly AnnonceFormValidator $validator,
        private readonly AnnonceFormMapper $mapper,
    ) {
    }

    public function dispatch(Request $request): Response
    {
        $this->currentAction = $request->query('action', 'index') ?? 'index';
        $this->fragment = $request->isFragment();

        if ($request->isPost()) {
            return match ($this->currentAction) {
                'store'  => $this->store($request),
                'update' => $this->update($request),
                'delete' => $this->delete($request),
                default  => new Response('Action POST inconnue.', 405),
            };
        }

        return match ($this->currentAction) {
            'index'  => $this->index(),
            'show'   => $this->show($request),
            'create' => $this->create($request),
            'edit'   => $this->edit($request),
            default  => $this->notFound(),
        };
    }

    private function index(): Response
    {
        return $this->html('annonces/index', [
            'title'            => 'Toutes les annonces',
            'catalogue'        => $this->cataloguePresenter->presenter($this->repository->findAll()),
            'categorieOptions' => $this->categorieOptions(),
        ]);
    }

    private function show(Request $request): Response
    {
        $annonce = $this->findAnnonceFromQuery($request);
        if ($annonce === null) {
            return $this->notFound();
        }

        return $this->html('annonces/show', [
            'title'       => 'Annonce #' . $annonce->getId(),
            'annonceView' => $this->annoncePresenter->presenter($annonce),
        ]);
    }

    private function create(Request $request): Response
    {
        $formData = $this->validator->defaultFormData();
        $selectedBienId = $request->query('bien_id');
        if ($selectedBienId !== null && $this->bienRepository->findUnlinkedById($selectedBienId) !== null) {
            $formData['bien_mode'] = 'existing';
            $formData['bien_id'] = $selectedBienId;
        }

        return $this->renderForm('Nouvelle annonce', '?action=store', $formData, []);
    }

    private function store(Request $request): Response
    {
        $attachableBienIds = array_keys($this->bienOptions());
        $result = $this->validator->validate($request->postAll(), true, $attachableBienIds);
        if ($result->hasErrors()) {
            return $this->renderForm('Nouvelle annonce', '?action=store', $result->formData, $result->errors, 422);
        }

        $id = $this->repository->create($result->data);

        return $this->redirectTo('?action=show&id=' . $id, 201);
    }

    private function edit(Request $request): Response
    {
        $annonce = $this->findAnnonceFromQuery($request);
        if ($annonce === null) {
            return $this->notFound();
        }

        return $this->renderForm(
            'Modifier annonce',
            '?action=update&id=' . $annonce->getId(),
            $this->mapper->formDataFromAnnonce($annonce),
            [],
            200,
            true,
        );
    }

    private function update(Request $request): Response
    {
        $annonce = $this->findAnnonceFromQuery($request);
        if ($annonce === null) {
            return $this->notFound();
        }

        $post   = $this->mapper->applyLockedPost($annonce, $request->postAll());
        $result = $this->validator->validate($post);

        if ($result->hasErrors()) {
            return $this->renderForm(
                'Modifier annonce',
                '?action=update&id=' . $annonce->getId(),
                $result->formData,
                $result->errors,
                422,
                true,
            );
        }

        $data = $this->mapper->forceLockedFields($annonce, $result->data);

        $this->appliquerChangementPrix($annonce, $data);
        $this->appliquerChangementLoyer($annonce, $data);
        $this->appliquerChangementEtat($annonce, $data);

        $this->repository->update($annonce->getId(), $data);

        return $this->redirectTo('?action=show&id=' . $annonce->getId());
    }

    private function appliquerChangementPrix(Annonce $annonce, array $data): void
    {
        if (!$annonce instanceof AnnonceVente || $data['prix_courant'] === null) {
            return;
        }
        if (bccomp($data['prix_courant'], $annonce->getPrixCourant(), 2) === 0) {
            return;
        }

        try {
            $this->prixService->modifierPrix($annonce->getId(), $data['prix_courant']);
        } catch (RuntimeException) {
        }
    }

    private function appliquerChangementLoyer(Annonce $annonce, array $data): void
    {
        if (!$annonce instanceof AnnonceLocation || $data['loyer'] === null) {
            return;
        }

        $this->loyerService->changer($annonce, (string) $data['loyer']);
    }

    private function appliquerChangementEtat(Annonce $annonce, array $data): void
    {
        $nouvelEtat = EtatAnnonce::tryFrom((string) $data['etat']);
        if ($nouvelEtat !== null) {
            $this->etatService->changer($annonce, $nouvelEtat);
        }
    }

    private function delete(Request $request): Response
    {
        $annonce = $this->findAnnonceFromQuery($request);
        if ($annonce === null) {
            return $this->notFound();
        }

        $this->repository->delete($annonce->getId());

        return $this->redirectTo(self::ACTION_INDEX);
    }

    private function renderForm(string $title, string $action, array $formData, array $errors, int $status = 200, bool $isEdit = false): Response
    {
        return $this->html('annonces/form', [
            'title'              => $title,
            'action'             => $action,
            'formData'           => $formData,
            'errors'             => $errors,
            'isEdit'             => $isEdit,
            'categorieOptions'   => $this->categorieOptions(),
            'typeOptions'        => $this->typeOptions(),
            'villeOptions'       => $this->villeOptions(),
            'transactionOptions' => $this->transactionOptions(),
            'etatOptions'        => $this->etatOptions(),
            'bienOptions'        => $isEdit ? [] : $this->bienOptions(),
        ], $status);
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

    private function villeOptions(): array
    {
        $options = [];
        foreach (Ville::cases() as $ville) {
            $options[$ville->value] = $ville->libelle();
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

    private function bienOptions(): array
    {
        $options = [];
        foreach ($this->bienRepository->findUnlinked() as $bien) {
            $label = sprintf(
                '%s - %s%s - %.0f m²',
                $bien->getVille(),
                $bien->getCategorie()->libelle(),
                $bien->getType() !== null ? ' ' . $bien->getType()->libelle() : '',
                $bien->getSurface(),
            );
            $options[$bien->getId()] = $label;
        }

        return $options;
    }

    private function html(string $template, array $params = [], int $status = 200): Response
    {
        $params += [
            'currentAction' => $this->currentAction,
            'isFragment'    => $this->fragment,
        ];
        $layout = $this->fragment ? null : 'layout';

        return new Response($this->views->render($template, $params, $layout), $status);
    }

    private function redirectTo(string $location, int $jsonStatus = 200): Response
    {
        return $this->fragment
            ? Response::json(['redirect' => $location], $jsonStatus)
            : Response::redirect($location);
    }

    private function notFound(): Response
    {
        return $this->html('errors/404', ['title' => 'Page introuvable'], 404);
    }

    private function findAnnonceFromQuery(Request $request): ?Annonce
    {
        $id = $request->queryInt('id');

        return $id === null ? null : $this->repository->findById($id);
    }
}
