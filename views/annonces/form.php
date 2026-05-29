<?php
$value = fn(string $key): string => $this->e($formData[$key] ?? '');
$selected = fn(string $key, string $expected): string => ($formData[$key] ?? '') === $expected ? ' selected' : '';
$error = static function (string $key) use ($errors): string {
    if (!isset($errors[$key])) {
        return '';
    }

    return '<p class="mt-1 text-xs font-medium text-red-600">' . htmlspecialchars($errors[$key], ENT_QUOTES, 'UTF-8') . '</p>';
};

$inputClass    = 'mt-1 block w-full px-3 py-2 text-sm rounded-md border border-beige-200 bg-white text-stone-800 placeholder:text-stone-400 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors';
$readonlyClass = 'mt-1 block w-full px-3 py-2 text-sm rounded-md border border-beige-100 bg-beige-50 text-stone-500 cursor-not-allowed';
$labelClass    = 'block text-xs font-semibold uppercase tracking-wide text-stone-500';

$fragment = $isFragment ?? false;
$isEdit   = $isEdit ?? false;
$bienMode = $formData['bien_mode'] ?? 'new';
$bienOptions = $bienOptions ?? [];
$hasBienOptions = $bienOptions !== [];

$lockedAttr = $isEdit ? ' disabled' : '';
$lockedCls  = $isEdit ? $readonlyClass : $inputClass;
?>

<?php if (!$fragment): ?>
<section class="mb-5">
    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">CRUD</p>
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
        <legend class="text-sm font-bold text-stone-900 mb-2">Annonce</legend>

        <label class="block">
            <span class="<?= $labelClass ?>">Titre</span>
            <input name="titre" value="<?= $value('titre') ?>" maxlength="160"
                   placeholder="Ex. Belle maison dans quartier tranquille"
                   class="<?= $inputClass ?>">
            <?= $error('titre') ?>
        </label>

        <label class="block">
            <span class="<?= $labelClass ?>">Description</span>
            <textarea name="description" rows="3" class="<?= $inputClass ?>"><?= $value('description') ?></textarea>
            <?= $error('description') ?>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="block">
                <span class="<?= $labelClass ?>">Transaction</span>
                <select name="transaction" data-annonce-transaction class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                    <?php foreach ($transactionOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('transaction', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $error('transaction') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">État</span>
                <select name="etat" class="<?= $inputClass ?>">
                    <?php foreach ($etatOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('etat', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $error('etat') ?>
            </label>

            <?php if ($isEdit): ?>
                <label class="block" data-transaction-field="vente">
                    <span class="<?= $labelClass ?>">Prix initial (€)</span>
                    <input name="prix_initial" value="<?= $value('prix_initial') ?>" inputmode="decimal" class="<?= $readonlyClass ?>" disabled>
                </label>

                <label class="block" data-transaction-field="vente">
                    <span class="<?= $labelClass ?>">Prix actuel (€)</span>
                    <input name="prix_courant" value="<?= $value('prix_courant') ?>" inputmode="decimal" placeholder="250000.00" class="<?= $inputClass ?>">
                    <?= $error('prix_courant') ?>
                </label>
            <?php else: ?>
                <label class="block sm:col-span-2" data-transaction-field="vente">
                    <span class="<?= $labelClass ?>">Prix de vente (€)</span>
                    <input name="prix_initial" value="<?= $value('prix_initial') ?>" inputmode="decimal" placeholder="250000.00" class="<?= $inputClass ?>">
                    <?= $error('prix_initial') ?>
                </label>
            <?php endif; ?>

            <?php if ($isEdit): ?>
                <label class="block" data-transaction-field="location">
                    <span class="<?= $labelClass ?>">Loyer initial (€/mois)</span>
                    <input name="loyer_initial" value="<?= $value('loyer_initial') ?>" inputmode="decimal" class="<?= $readonlyClass ?>" disabled>
                </label>

                <label class="block" data-transaction-field="location">
                    <span class="<?= $labelClass ?>">Loyer actuel (€/mois)</span>
                    <input name="loyer" value="<?= $value('loyer') ?>" inputmode="decimal" placeholder="850.00" class="<?= $inputClass ?>">
                    <?= $error('loyer') ?>
                </label>

                <label class="block" data-transaction-field="location">
                    <span class="<?= $labelClass ?>">Charges initiales (€/mois)</span>
                    <input name="charges_initiales" value="<?= $value('charges_initiales') ?>" inputmode="decimal" class="<?= $readonlyClass ?>" disabled>
                </label>

                <label class="block" data-transaction-field="location">
                    <span class="<?= $labelClass ?>">Charges actuelles (€/mois)</span>
                    <input name="charges" value="<?= $value('charges') ?>" inputmode="decimal" placeholder="90.00" class="<?= $inputClass ?>">
                    <?= $error('charges') ?>
                </label>
            <?php else: ?>
                <label class="block" data-transaction-field="location">
                    <span class="<?= $labelClass ?>">Loyer (€/mois)</span>
                    <input name="loyer" value="<?= $value('loyer') ?>" inputmode="decimal" placeholder="850.00" class="<?= $inputClass ?>">
                    <?= $error('loyer') ?>
                </label>

                <label class="block" data-transaction-field="location">
                    <span class="<?= $labelClass ?>">Charges (€/mois)</span>
                    <input name="charges" value="<?= $value('charges') ?>" inputmode="decimal" placeholder="90.00" class="<?= $inputClass ?>">
                    <?= $error('charges') ?>
                </label>
            <?php endif; ?>
        </div>
    </fieldset>

    <fieldset class="space-y-4">
        <legend class="text-sm font-bold text-stone-900 mb-2">Bien immobilier</legend>

        <?php if (!$isEdit): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-3 rounded-md border border-beige-200 bg-white px-3 py-3 text-sm text-stone-700">
                    <input type="radio" name="bien_mode" value="new" data-bien-mode class="mt-1 accent-khaki-600"<?= $bienMode !== 'existing' ? ' checked' : '' ?>>
                    <span>
                        <span class="block font-semibold text-stone-900">Créer un bien</span>
                        <span class="block text-xs text-stone-500">Renseigner les informations du bien avec l'annonce.</span>
                    </span>
                </label>
                <label class="flex items-start gap-3 rounded-md border border-beige-200 bg-white px-3 py-3 text-sm text-stone-700 <?= !$hasBienOptions ? 'opacity-50' : '' ?>">
                    <input type="radio" name="bien_mode" value="existing" data-bien-mode class="mt-1 accent-khaki-600"<?= $bienMode === 'existing' ? ' checked' : '' ?><?= !$hasBienOptions ? ' disabled' : '' ?>>
                    <span>
                        <span class="block font-semibold text-stone-900">Attacher un bien existant</span>
                        <span class="block text-xs text-stone-500"><?= $hasBienOptions ? 'Utiliser un bien sans annonce.' : 'Aucun bien disponible pour le moment.' ?></span>
                    </span>
                </label>
            </div>

            <div class="rounded-md border border-beige-200 bg-beige-50/50 p-4"
                 data-bien-mode-field="existing"<?= $bienMode === 'existing' ? '' : ' hidden' ?>>
                <label class="block">
                    <span class="<?= $labelClass ?>">Bien disponible</span>
                    <select name="bien_id" class="<?= $inputClass ?>">
                        <option value="">- Sélectionner un bien -</option>
                        <?php foreach ($bienOptions as $optValue => $optLibelle): ?>
                            <option value="<?= $this->e($optValue) ?>"<?= $selected('bien_id', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $error('bien_id') ?>
                </label>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"<?= !$isEdit && $bienMode === 'existing' ? ' hidden' : '' ?> data-bien-mode-field="new">
            <label class="block">
                <span class="<?= $labelClass ?>">Catégorie</span>
                <select name="categorie" data-annonce-categorie class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                    <?php foreach ($categorieOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('categorie', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $error('categorie') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Type (pièces principales)</span>
                <select name="type" class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                    <option value="">— Non précisé —</option>
                    <?php foreach ($typeOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('type', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$isEdit): ?>
                    <span class="mt-1 block text-[0.7rem] text-stone-400">Studio = 1 pièce. T<i>n</i> = salon + (<i>n</i>−1) chambres.</span>
                <?php endif; ?>
                <?= $error('type') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Ville</span>
                <?php $villeCourante = (string) ($formData['ville'] ?? ''); ?>
                <select name="ville" class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                    <option value="">— Sélectionner une ville —</option>
                    <?php foreach ($villeOptions as $optValue => $optLibelle): ?>
                        <option value="<?= $this->e($optValue) ?>"<?= $selected('ville', $optValue) ?>><?= $this->e($optLibelle) ?></option>
                    <?php endforeach; ?>
                    <?php if ($villeCourante !== '' && !isset($villeOptions[$villeCourante])): ?>
                        <option value="<?= $this->e($villeCourante) ?>" selected><?= $this->e($villeCourante) ?></option>
                    <?php endif; ?>
                </select>
                <?= $error('ville') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Surface (m²)</span>
                <input name="surface" value="<?= $value('surface') ?>" inputmode="decimal" placeholder="75.00" class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                <?= $error('surface') ?>
            </label>

            <label class="block">
                <span class="<?= $labelClass ?>">Chambres</span>
                <input name="chambres" value="<?= $value('chambres') ?>" inputmode="numeric" placeholder="3" class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                <?= $error('chambres') ?>
            </label>

            <label class="block" data-categorie-field="appartement">
                <span class="<?= $labelClass ?>">Étage</span>
                <input name="etage" value="<?= $value('etage') ?>" inputmode="numeric" class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
                <?= $error('etage') ?>
            </label>

            <label class="block" data-categorie-field="maison,villa">
                <span class="<?= $labelClass ?>">Terrain (m²)</span>
                <input name="terrain" value="<?= $value('terrain') ?>" inputmode="decimal" placeholder="300.00" class="<?= $lockedCls ?>"<?= $lockedAttr ?>>
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
            <a href="?action=index"
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
