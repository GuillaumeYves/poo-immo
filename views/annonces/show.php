<?php
$prix = $annonceView['prix'];

$etatStyles = [
    'disponible'     => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'en_negociation' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'indisponible'   => 'bg-stone-100 text-stone-600 ring-stone-200',
];
$etatClass = $etatStyles[$annonceView['etat']] ?? 'bg-stone-100 text-stone-600 ring-stone-200';

$transactionClass = $annonceView['transaction'] === 'vente'
    ? 'bg-khaki-500 text-white'
    : 'bg-stone-800 text-white';

$iconClass = $annonceView['categorie'] === 'appartement' ? 'fa-building' : 'fa-house';

$badgeBits = array_filter([
    $annonceView['categorieLibelle'] ?? null,
    $annonceView['typeLibelle'] ?? null,
]);
$badgeImage = implode(' · ', $badgeBits);

$prixSensStyles = [
    'reduction' => ['color' => 'text-emerald-700', 'icon' => 'fa-arrow-trend-down', 'signe' => '-'],
    'hausse'    => ['color' => 'text-red-700',     'icon' => 'fa-arrow-trend-up',   'signe' => '+'],
];
?>

<section class="mb-5 flex items-center justify-between gap-3">
    <a href="?action=index"
       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md text-stone-600 hover:bg-beige-100 hover:text-stone-900 hover:-translate-x-0.5 transition-all duration-200 ease-soft">
        <i class="fa-solid fa-arrow-left text-xs"></i>
        Retour
    </a>

    <div class="flex flex-wrap items-center gap-2">
        <button type="button"
                data-modal-open="?action=edit&id=<?= $this->e($annonceView['id']) ?>&fragment=1"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md border border-beige-200 bg-white text-stone-700 hover:border-khaki-400 hover:text-khaki-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
            <i class="fa-solid fa-pen-to-square text-xs"></i>
            Modifier
        </button>

        <button type="button"
                data-modal-template="confirm-delete"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md border border-red-200 bg-red-50 text-red-700 hover:border-red-400 hover:bg-red-100 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
            <i class="fa-solid fa-trash-can text-xs"></i>
            Supprimer
        </button>
    </div>
</section>

<article class="bg-white border border-beige-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-5">
        <div class="relative lg:col-span-3 bg-gradient-to-br from-beige-100 to-beige-200 flex items-center justify-center min-h-[260px] sm:min-h-[340px] lg:min-h-[420px]">
            <i class="fa-solid <?= $iconClass ?> text-[8rem] sm:text-[10rem] text-khaki-600/35 transition-transform duration-700 ease-soft hover:scale-105"></i>

            <span class="absolute top-4 left-4 px-3 py-1 text-xs font-semibold uppercase tracking-wide rounded <?= $transactionClass ?>">
                <?= $this->e($annonceView['transactionLibelle']) ?>
            </span>

            <?php if ($badgeImage !== ''): ?>
                <span class="absolute top-4 right-4 px-3 py-1 text-xs font-semibold uppercase tracking-wide rounded bg-white/95 text-stone-700 ring-1 ring-beige-200">
                    <?= $this->e($badgeImage) ?>
                </span>
            <?php endif; ?>
        </div>

        <aside class="lg:col-span-2 p-6 sm:p-8 flex flex-col gap-5 border-t lg:border-t-0 lg:border-l border-beige-100">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400"><?= $this->e($annonceView['ville']) ?></p>
                <h2 class="mt-1 text-xl font-bold text-stone-900"><?= $this->e($annonceView['titre']) ?></h2>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full ring-1 <?= $etatClass ?>"><?= $this->e($annonceView['etatLibelle']) ?></span>
                    <span class="text-xs text-stone-500"><?= $this->e($annonceView['meta'][1] ?? '') ?></span>
                </div>
            </div>

            <?php if ($prix['prixCourant'] !== null): ?>
                <div>
                    <?php if ($prix['sens'] === 'aucune'): ?>
                        <p class="text-3xl sm:text-4xl font-bold text-stone-900"><?= $this->e($prix['prixCourant']) ?></p>
                    <?php else:
                        $style = $prixSensStyles[$prix['sens']];
                    ?>
                        <p class="text-sm text-stone-400 line-through"><?= $this->e($prix['prixInitial']) ?></p>
                        <p class="mt-0.5 inline-flex items-center gap-2 text-3xl sm:text-4xl font-bold <?= $style['color'] ?>">
                            <i class="fa-solid <?= $style['icon'] ?> text-2xl"></i>
                            <span><?= $this->e($prix['prixCourant']) ?></span>
                            <span class="text-base font-semibold">(<?= $style['signe'] ?><?= $this->e($prix['pourcentage']) ?>%)</span>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 pt-4 border-t border-beige-100">
                <?php foreach ($annonceView['attributs'] as $attr): ?>
                    <div class="min-w-0">
                        <dt class="text-[0.7rem] uppercase tracking-wide text-stone-400"><?= $this->e($attr[0]) ?></dt>
                        <dd class="mt-0.5 text-sm font-semibold text-stone-800 truncate">
                            <?php if (isset($attr[2])): ?>
                                <?= $attr[2] ?>
                            <?php else: ?>
                                <?= $this->e($attr[1]) ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </aside>
    </div>

    <?php $description = $annonceView['description']; ?>
    <?php if ($description !== null && trim($description) !== ''): ?>
        <section class="px-6 sm:px-8 py-6 border-t border-beige-100">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-400">Description</h3>
            <p class="mt-2 text-sm text-stone-700 whitespace-pre-line leading-relaxed"><?= $this->e($description) ?></p>
        </section>
    <?php endif; ?>
</article>

<template id="confirm-delete">
    <h2 class="text-xl font-bold text-stone-900">Supprimer cette annonce ?</h2>
    <p class="mt-2 text-sm text-stone-600">
        Cette action est définitive. L'annonce <strong><?= $this->e($annonceView['titre']) ?></strong> sera retirée du catalogue.
    </p>
    <form data-modal-form method="post"
          action="?action=delete&id=<?= $this->e($annonceView['id']) ?>&fragment=1"
          class="mt-5 flex justify-end gap-2">
        <button type="button" data-modal-close
                class="px-3 py-2 text-sm font-medium rounded-md text-stone-600 hover:bg-beige-100 transition-colors">
            Annuler
        </button>
        <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-md bg-red-600 text-white hover:bg-red-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
            <i class="fa-solid fa-trash-can text-xs"></i>
            Confirmer
        </button>
    </form>
</template>
