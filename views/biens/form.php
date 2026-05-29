<?php
$value = fn(string $key): string => $this->e($formData[$key] ?? '');
$selected = fn(string $key, string $expected): string => ($formData[$key] ?? '') === $expected ? ' selected' : '';
$error = static function (string $key) use ($errors): string {
    if (!isset($errors[$key])) {
        return '';
    }

    return '<p class="mt-1 text-xs font-medium text-red-600">' . htmlspecialchars($errors[$key], ENT_QUOTES, 'UTF-8') . '</p>';
};

$inputClass = 'mt-1 block w-full px-3 py-2 text-sm rounded-md border border-beige-200 bg-white text-stone-800 placeholder:text-stone-400 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors';
$labelClass = 'block text-xs font-semibold uppercase tracking-wide text-stone-500';
$fragment = $isFragment ?? false;
?>

<?php if (!$fragment): ?>
<section class="mb-5">
    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Biens</p>
    <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-stone-900"><?= $this->e($title) ?></h1>
</section>
<?php else: ?>
<h2 class="text-xl font-bold text-stone-900 mb-5"><?= $this->e($title) ?></h2>
<?php endif; ?>

<?php if ($errors !== []): ?>
    <div class="mb-4 px-4 py-3 rounded-md border border-red-200 bg-red-50 text-sm text-red-700" role="alert">
        Le formulaire contient des erreurs.
    </div>
<?php endif; ?>

<form data-modal-form method="post" action="<?= $this->e($action) ?>" class="space-y-6">
    <fieldset class="space-y-4">
        <legend class="text-sm font-bold text-stone-900 mb-2">Bien immobilier</legend>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="block">
                <span class="<?= $labelClass ?>">Catégorie</span>
                <select name="categorie" data-annonce-categorie class="<?= $inputClass ?>">
                    <?php foreach ($categorieOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('categorie', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $error('categorie') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Type (pièces principales)</span>
                <select name="type" class="<?= $inputClass ?>">
                    <option value="">- Non précisé -</option>
                    <?php foreach ($typeOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('type', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="mt-1 block text-[0.7rem] text-stone-400">Studio = 1 pièce. T<i>n</i> = salon + (<i>n</i>-1) chambres.</span>
                <?= $error('type') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Ville</span>
                <select name="ville" class="<?= $inputClass ?>">
                    <option value="">- Sélectionner une ville -</option>
                    <?php foreach ($villeOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('ville', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $error('ville') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Surface (m²)</span>
                <input name="surface" value="<?= $value('surface') ?>" inputmode="decimal" placeholder="75.00" class="<?= $inputClass ?>">
                <?= $error('surface') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Chambres</span>
                <input name="chambres" value="<?= $value('chambres') ?>" inputmode="numeric" placeholder="3" class="<?= $inputClass ?>">
                <?= $error('chambres') ?>
            </label>

            <label class="block" data-categorie-field="appartement">
                <span class="<?= $labelClass ?>">Étage</span>
                <input name="etage" value="<?= $value('etage') ?>" inputmode="numeric" class="<?= $inputClass ?>">
                <?= $error('etage') ?>
            </label>

            <label class="block" data-categorie-field="maison,villa">
                <span class="<?= $labelClass ?>">Terrain (m²)</span>
                <input name="terrain" value="<?= $value('terrain') ?>" inputmode="decimal" placeholder="300.00" class="<?= $inputClass ?>">
                <?= $error('terrain') ?>
            </label>
        </div>
    </fieldset>

    <div class="flex items-center justify-end gap-2 pt-2 border-t border-beige-100">
        <?php if ($fragment): ?>
            <button type="button" data-modal-close
                    class="px-3 py-2 text-sm font-medium rounded-md text-stone-600 hover:bg-beige-100 transition-colors">
                Annuler
            </button>
        <?php else: ?>
            <a href="?action=biens"
               class="px-3 py-2 text-sm font-medium rounded-md text-stone-600 hover:bg-beige-100 transition-colors">
                Annuler
            </a>
        <?php endif; ?>
        <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-md bg-khaki-500 text-white shadow-sm hover:bg-khaki-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
            Enregistrer
        </button>
    </div>
</form>
