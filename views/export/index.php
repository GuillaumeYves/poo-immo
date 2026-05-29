<?php
$value = fn(?string $v): string => $this->e($v ?? '');
$annonceDatasetJson = json_encode(
    $annonceDataset ?? [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR,
);
$bienDatasetJson = json_encode(
    $bienDataset ?? [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR,
);
$inputClass = 'mt-1 block w-full px-3 py-2 text-sm rounded-md border border-beige-200 bg-white text-stone-800 placeholder:text-stone-400 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors';
$labelClass = 'block text-xs font-semibold uppercase tracking-wide text-stone-500';
$picker = fn(string $name, string $label, array $options, array $selected, string $placeholder, string $allLabel) =>
    $this->partial('_multi_picker', [
        'name'        => $name,
        'label'       => $label,
        'options'     => $options,
        'selected'    => $selected,
        'placeholder' => $placeholder,
        'allLabel'    => $allLabel,
        'selectClass' => $inputClass,
    ]);
?>

<section class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-stone-900">Export</h1>
</section>

<section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <article class="bg-white border border-beige-200 rounded-xl shadow-sm p-5 sm:p-6"
             data-export-section
             data-multi-fields="categorie,type,transaction,ville,etat"
             data-max-field="prix_max"
             data-max-kind="montant"
             data-count-singular="annonce"
             data-count-plural="annonces">
        <script type="application/json" data-export-dataset><?= $annonceDatasetJson ?></script>

        <div class="mb-5 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-stone-900">Annonces</h2>
            <p class="text-sm font-semibold text-stone-500" data-export-count>
                <?= $annoncesTotal ?> annonce<?= $annoncesTotal > 1 ? 's' : '' ?>
            </p>
        </div>

        <form method="get" data-export-form>
            <input type="hidden" name="action" value="export">
            <input type="hidden" name="target" value="annonces">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php $picker('categorie', 'Catégorie', $categorieOptions, (array) ($annonceFilters['categorie'] ?? []), 'Ajouter une catégorie', 'Toutes les catégories'); ?>
                <?php $picker('type', 'Type (pièces)', $typeOptions, (array) ($annonceFilters['type'] ?? []), 'Ajouter un type', 'Tous les types'); ?>
                <?php $picker('transaction', 'Transaction', $transactionOptions, (array) ($annonceFilters['transaction'] ?? []), 'Ajouter une transaction', 'Toutes les transactions'); ?>
                <?php $picker('ville', 'Villes', $villeOptions, (array) ($annonceFilters['ville'] ?? []), 'Ajouter une ville', 'Toutes les villes'); ?>
                <?php $picker('etat', 'État', $etatOptions, (array) ($annonceFilters['etat'] ?? []), 'Ajouter un état', 'Tous les états'); ?>

                <label class="block">
                    <span class="<?= $labelClass ?>">Prix max (€)</span>
                    <input name="prix_max" value="<?= $value($annonceFilters['prixMax']) ?>" inputmode="decimal" placeholder="200000" class="<?= $inputClass ?>">
                </label>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-end gap-2 pt-4 border-t border-beige-100">
                <a href="?action=export"
                   class="px-3 py-2 text-sm font-medium rounded-md text-stone-600 hover:bg-beige-100 transition-colors">
                    Réinitialiser
                </a>
                <?php foreach ($annonceExporters as $format => $exporter): ?>
                    <a href="?action=export&target=annonces&download=1&format=<?= $this->e((string) $format) ?>"
                       data-export-download
                       data-format="<?= $this->e((string) $format) ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-md border <?= $format === 'csv' ? 'border-beige-200 bg-white text-stone-700 hover:border-khaki-400 hover:text-khaki-700' : 'border-khaki-500 bg-khaki-500 text-white hover:bg-khaki-600' ?> hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
                        <i class="fa-solid fa-download text-xs"></i>
                        <?= strtoupper($this->e((string) $format)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </article>

    <article class="bg-white border border-beige-200 rounded-xl shadow-sm p-5 sm:p-6"
             data-export-section
             data-multi-fields="categorie,type,ville"
             data-max-field="surface_max"
             data-max-kind="surface"
             data-count-singular="bien"
             data-count-plural="biens">
        <script type="application/json" data-export-dataset><?= $bienDatasetJson ?></script>

        <div class="mb-5 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-stone-900">Biens sans annonce</h2>
            <p class="text-sm font-semibold text-stone-500" data-export-count>
                <?= $biensTotal ?> bien<?= $biensTotal > 1 ? 's' : '' ?>
            </p>
        </div>

        <form method="get" data-export-form>
            <input type="hidden" name="action" value="export">
            <input type="hidden" name="target" value="biens">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php $picker('categorie', 'Catégorie', $categorieOptions, (array) ($bienFilters['categorie'] ?? []), 'Ajouter une catégorie', 'Toutes les catégories'); ?>
                <?php $picker('type', 'Type (pièces)', $typeOptions, (array) ($bienFilters['type'] ?? []), 'Ajouter un type', 'Tous les types'); ?>
                <?php $picker('ville', 'Villes', $villeOptions, (array) ($bienFilters['ville'] ?? []), 'Ajouter une ville', 'Toutes les villes'); ?>

                <label class="block">
                    <span class="<?= $labelClass ?>">Surface max (m²)</span>
                    <input name="surface_max" value="<?= $value($bienFilters['surfaceMax']) ?>" inputmode="decimal" placeholder="120" class="<?= $inputClass ?>">
                </label>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-end gap-2 pt-4 border-t border-beige-100">
                <a href="?action=export"
                   class="px-3 py-2 text-sm font-medium rounded-md text-stone-600 hover:bg-beige-100 transition-colors">
                    Réinitialiser
                </a>
                <?php foreach ($bienExporters as $format => $exporter): ?>
                    <a href="?action=export&target=biens&download=1&format=<?= $this->e((string) $format) ?>"
                       data-export-download
                       data-format="<?= $this->e((string) $format) ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-md border <?= $format === 'csv' ? 'border-beige-200 bg-white text-stone-700 hover:border-khaki-400 hover:text-khaki-700' : 'border-khaki-500 bg-khaki-500 text-white hover:bg-khaki-600' ?> hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
                        <i class="fa-solid fa-download text-xs"></i>
                        <?= strtoupper($this->e((string) $format)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </article>
</section>
