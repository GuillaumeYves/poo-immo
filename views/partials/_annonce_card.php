<?php
$etatStyles = [
    'disponible'     => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'en_negociation' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'indisponible'   => 'bg-stone-100 text-stone-600 ring-stone-200',
];
$etatClass = $etatStyles[$annonce['etat']] ?? 'bg-stone-100 text-stone-600 ring-stone-200';

$transactionClass = $annonce['transaction'] === 'vente'
    ? 'bg-khaki-500 text-white'
    : 'bg-stone-800 text-white';

$iconClass = $annonce['categorie'] === 'appartement' ? 'fa-building' : 'fa-house';

$badgeBits = array_filter([
    $annonce['categorieLibelle'] ?? null,
    $annonce['typeLibelle'] ?? null,
]);
$badgeImage = implode(' · ', $badgeBits);

$prix = $annonce['prix'];
$prixSensStyles = [
    'reduction' => ['color' => 'text-emerald-700', 'icon' => 'fa-arrow-trend-down', 'signe' => '-'],
    'hausse'    => ['color' => 'text-red-700',     'icon' => 'fa-arrow-trend-up',   'signe' => '+'],
];

$haystack = mb_strtolower(implode(' ', array_filter([
    $annonce['titre'],
    $annonce['ville'],
    $annonce['type'],
    $annonce['typeLibelle'],
    $annonce['categorie'],
    $annonce['categorieLibelle'],
])));
?>

<article class="annonce-card group relative bg-white border border-beige-200 rounded-xl overflow-hidden shadow-sm hover:shadow-lg hover:border-khaki-400 hover:-translate-y-0.5 transition-all duration-300 ease-soft"
         data-titre="<?= $this->e($annonce['titre']) ?>"
         data-recherche="<?= $this->e($haystack) ?>"
         data-categorie="<?= $this->e($annonce['categorie']) ?>"
         data-transaction="<?= $this->e($annonce['transaction']) ?>"
         data-prix="<?= $this->e($prix['tri'] ?? '') ?>">
    <a href="?action=show&id=<?= $this->e($annonce['id']) ?>"
       class="annonce-card__layout flex no-underline"
       aria-label="Voir l'annonce <?= $this->e($annonce['titre']) ?>">

        <div class="annonce-card__image relative shrink-0 bg-gradient-to-br from-beige-100 to-beige-200 flex items-center justify-center overflow-hidden">
            <i class="fa-solid <?= $iconClass ?> text-5xl text-khaki-600/40 group-hover:text-khaki-600/60 group-hover:scale-110 transition-all duration-500 ease-soft"></i>

            <span class="absolute top-2 left-2 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide rounded <?= $transactionClass ?>">
                <?= $this->e($annonce['transactionLibelle']) ?>
            </span>

            <?php if ($badgeImage !== ''): ?>
                <span class="absolute top-2 right-2 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide rounded bg-white/95 text-stone-700 ring-1 ring-beige-200">
                    <?= $this->e($badgeImage) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="annonce-card__content flex-1 min-w-0 p-4 sm:p-5 flex flex-col gap-2">
            <div class="min-w-0">
                <p class="text-xs sm:text-sm font-medium text-stone-500 truncate">
                    <?= $this->e($annonce['ville']) ?>
                </p>
                <span class="mt-1 inline-flex px-2 py-0.5 text-[0.65rem] font-semibold rounded-full ring-1 <?= $etatClass ?>">
                    <?= $this->e($annonce['etatLibelle']) ?>
                </span>
                <h2 class="annonce-card__title mt-1.5 text-base sm:text-lg font-semibold text-stone-900 group-hover:text-khaki-600 transition-colors duration-200">
                    <?= $this->e($annonce['titre']) ?>
                </h2>
            </div>

            <p class="annonce-card__compact-hide text-xs sm:text-sm text-stone-500 truncate">
                <?= $this->e($annonce['meta'][1] ?? '') ?>
            </p>

            <dl class="annonce-card__compact-hide mt-1 grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-1.5 text-sm">
                <?php foreach ($annonce['attributs'] as $attr): ?>
                    <div class="min-w-0">
                        <dt class="text-[0.7rem] uppercase tracking-wide text-stone-400"><?= $this->e($attr[0]) ?></dt>
                        <dd class="font-medium text-stone-700 truncate">
                            <?php if (isset($attr[2])): ?>
                                <?= $attr[2] ?>
                            <?php else: ?>
                                <?= $this->e($attr[1]) ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endforeach; ?>
            </dl>

            <?php if ($prix['prixCourant'] !== null): ?>
                <div class="mt-auto pt-1">
                    <?php if ($prix['sens'] === 'aucune'): ?>
                        <p class="text-base sm:text-lg font-bold text-khaki-700"><?= $this->e($prix['prixCourant']) ?></p>
                    <?php else:
                        $style = $prixSensStyles[$prix['sens']];
                    ?>
                        <p class="text-xs text-stone-400 line-through"><?= $this->e($prix['prixInitial']) ?></p>
                        <p class="inline-flex items-center gap-1.5 text-base sm:text-lg font-bold <?= $style['color'] ?>">
                            <i class="fa-solid <?= $style['icon'] ?> text-sm"></i>
                            <span><?= $this->e($prix['prixCourant']) ?></span>
                            <span class="text-xs font-semibold">(<?= $style['signe'] ?><?= $this->e($prix['pourcentage']) ?>%)</span>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </a>
</article>
