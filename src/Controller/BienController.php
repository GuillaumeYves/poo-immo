<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\Bien\BienRepository;
use App\Model\Bien\CategorieBien;
use App\Model\Bien\TypeBien;
use App\Model\Bien\Ville;
use App\Model\Form\BienFormValidator;
use App\View\BienPresenter;
use App\View\ViewRenderer;

/* *
 * Contrôleur pour la gestion des biens immobiliers.
 */
final class BienController
{
    private string $currentAction = 'biens';
    private bool $fragment = false;

    public function __construct(
        private readonly BienRepository $repository,
        private readonly BienPresenter $presenter,
        private readonly ViewRenderer $views,
        private readonly BienFormValidator $validator,
    ) {
    }

    public function dispatch(Request $request): Response
    {
        $this->currentAction = $request->query('action', 'biens') ?? 'biens';
        $this->fragment = $request->isFragment();

        if ($request->isPost()) {
            return match ($this->currentAction) {
                'bien_store' => $this->store($request),
                default      => new Response('Action POST inconnue.', 405),
            };
        }

        return match ($this->currentAction) {
            'biens'       => $this->index(),
            'bien_create' => $this->create(),
            default       => $this->notFound(),
        };
    }

    private function index(): Response
    {
        $biens = $this->repository->findUnlinked();

        return $this->html('biens/index', [
            'title' => 'Biens sans annonce',
            'biens' => array_map(fn($bien): array => $this->presenter->presenter($bien), $biens),
        ]);
    }

    private function create(array $errors = [], ?array $formData = null, int $status = 200): Response
    {
        return $this->html('biens/form', [
            'title'            => 'Nouveau bien',
            'action'           => '?action=bien_store',
            'formData'         => $formData ?? $this->validator->defaultFormData(),
            'errors'           => $errors,
            'categorieOptions' => $this->categorieOptions(),
            'typeOptions'      => $this->typeOptions(),
            'villeOptions'     => $this->villeOptions(),
        ], $status);
    }

    private function store(Request $request): Response
    {
        $result = $this->validator->validate($request->postAll());
        if ($result->hasErrors()) {
            return $this->create($result->errors, $result->formData, 422);
        }

        $this->repository->create($result->data);

        return $this->redirectTo('?action=biens', 201);
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
}
