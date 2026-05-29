<?php
/**
 * Sélecteur multi-valeurs : un <select> qui sert à ajouter des éléments,
 * lesquels s'empilent en badges (avec input caché name="<name>[]").
 *
 * Paramètres attendus :
 * @var string $name        Nom du champ (ex. "ville", "categorie").
 * @var string $label       Libellé (visible si $showLabel, sinon aria-label).
 * @var array  $options     [valeur => libellé].
 * @var array  $selected    Valeurs déjà sélectionnées.
 * @var string $placeholder Texte de l'option vide (ex. "Ajouter une ville").
 * @var string $allLabel    Texte de l'option « tout sélectionner » (vide = pas d'option).
 * @var string $selectClass Classes CSS du <select>.
 * @var string $fieldClass  Classes CSS du conteneur.
 * @var bool   $showLabel   Affiche le libellé au-dessus (sinon sr-only).
 * @var string $fieldAttr   Attributs HTML supplémentaires sur le conteneur.
 */
$selected    = $selected ?? [];
$allLabel    = $allLabel ?? '';
$fieldAttr   = $fieldAttr ?? '';
$fieldClass  = $fieldClass ?? '';
$showLabel   = $showLabel ?? true;
$labelClass  = $labelClass ?? 'block text-xs font-semibold uppercase tracking-wide text-stone-500';
$badgeClass  = 'inline-flex max-w-full items-center gap-1 pl-2.5 pr-1 py-0.5 text-xs font-medium rounded-full bg-khaki-500/10 text-khaki-700 ring-1 ring-khaki-500/20';
$removeClass = 'inline-flex h-4 w-4 items-center justify-center rounded-full text-khaki-600 hover:bg-khaki-500/20 hover:text-khaki-800 transition-colors';
$optionValues = array_map('strval', array_keys($options));
$selectedValues = array_values(array_unique(array_map('strval', $selected)));
$allSelected = $allLabel !== '' && $optionValues !== [] && count(array_intersect($optionValues, $selectedValues)) === count($optionValues);
?>
<div data-multi-picker-field<?= $fieldClass !== '' ? ' class="' . $this->e($fieldClass) . '"' : '' ?> <?= $fieldAttr ?>>
    <?php if ($showLabel): ?>
        <span class="<?= $labelClass ?>"><?= $this->e($label) ?></span>
    <?php endif; ?>
    <select data-multi-picker="<?= $this->e($name) ?>"
            aria-label="<?= $this->e($label) ?>"
            class="<?= $selectClass ?><?= $allSelected ? ' opacity-60 cursor-not-allowed bg-beige-50 text-stone-400' : '' ?>"
            <?= $allSelected ? 'disabled' : '' ?>>
        <option value="" disabled hidden selected><?= $this->e($placeholder) ?></option>
        <?php if ($allLabel !== ''): ?>
            <option value="__all__" data-multi-picker-all<?= $allSelected ? ' hidden' : '' ?>><?= $this->e($allLabel) ?></option>
        <?php endif; ?>
        <?php foreach ($options as $optValue => $optLibelle): ?>
            <option value="<?= $this->e($optValue) ?>"<?= in_array((string) $optValue, $selected, true) ? ' hidden' : '' ?>><?= $this->e($optLibelle) ?></option>
        <?php endforeach; ?>
    </select>
    <div data-multi-picker-badges class="mt-2 flex flex-wrap gap-1.5">
        <?php foreach ($selected as $v): ?>
            <?php if (!isset($options[$v])) { continue; } ?>
            <span data-multi-picker-badge="<?= $this->e($v) ?>" class="<?= $badgeClass ?>">
                <input type="hidden" name="<?= $this->e($name) ?>[]" value="<?= $this->e($v) ?>">
                <span class="min-w-0 truncate"><?= $this->e($options[$v]) ?></span>
                <button type="button" data-multi-picker-remove aria-label="Retirer <?= $this->e($options[$v]) ?>" class="<?= $removeClass ?>">
                    <i class="fa-solid fa-xmark text-[0.6rem]"></i>
                </button>
            </span>
        <?php endforeach; ?>
    </div>
</div>
