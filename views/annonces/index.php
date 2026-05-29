<section class="mb-2 sm:mb-3">
    <p class="text-right text-xs sm:text-sm text-stone-500" id="recherche-compteur">
        <?= count($catalogue['annonces']) ?> annonce<?= count($catalogue['annonces']) > 1 ? 's' : '' ?>
    </p>
</section>

<section class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
    <form role="search" onsubmit="return false"
          class="flex items-center gap-2 w-full sm:max-w-xs">
        <label for="recherche-input" class="sr-only">Rechercher</label>
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-stone-400"></i>
            <input type="search"
                   id="recherche-input"
                   name="q"
                   autocomplete="off"
                   spellcheck="false"
                   placeholder="Ville, titre, type ou catégorie"
                   class="w-full pl-8 pr-3 py-2 text-sm rounded-md border border-beige-200 bg-white text-stone-800 placeholder:text-stone-400 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors">
        </div>
    </form>

    <div class="flex flex-wrap items-start justify-end gap-3">
        <?php $this->partial('_multi_picker', [
            'name'        => 'categorie',
            'label'       => 'Filtrer par catégorie',
            'options'     => $categorieOptions,
            'selected'    => [],
            'placeholder' => 'Catégorie',
            'allLabel'    => 'Toutes les catégories',
            'showLabel'   => false,
            'fieldAttr'   => 'data-catalogue-categorie',
            'fieldClass'  => 'w-full sm:w-48 shrink-0',
            'selectClass' => 'w-full py-2 pl-3 text-sm rounded-md border border-beige-200 bg-white text-stone-700 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors',
        ]); ?>

        <label class="sr-only" for="filtre-transaction">Filtrer par transaction</label>
        <select id="filtre-transaction"
                data-default-value="tout"
                class="py-2 pl-3 pr-8 text-sm rounded-md border border-beige-200 bg-white text-stone-700 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors">
            <option value="tout">Transaction&nbsp;: toutes</option>
            <option value="vente">Ventes</option>
            <option value="location">Locations</option>
        </select>

        <label class="sr-only" for="tri-prix">Trier par prix</label>
        <select id="tri-prix"
                data-default-value="defaut"
                class="py-2 pl-3 pr-8 text-sm rounded-md border border-beige-200 bg-white text-stone-700 focus:border-khaki-500 focus:ring-2 focus:ring-khaki-400/30 focus:outline-none transition-colors">
            <option value="defaut">Trier&nbsp;: par défaut</option>
            <option value="asc">Prix croissant</option>
            <option value="desc">Prix décroissant</option>
        </select>

        <div class="hidden sm:inline-flex rounded-md border border-beige-200 bg-white p-0.5" role="group" aria-label="Mode d'affichage">
            <button type="button" data-view-toggle="cartouche"
                    aria-pressed="true"
                    class="view-toggle-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded text-stone-500 transition-colors duration-200">
                <i class="fa-solid fa-list text-xs"></i>
                <span class="hidden sm:inline">Liste</span>
            </button>
            <button type="button" data-view-toggle="grille"
                    aria-pressed="false"
                    class="view-toggle-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded text-stone-500 transition-colors duration-200">
                <i class="fa-solid fa-grip text-xs"></i>
                <span class="hidden sm:inline">Grille</span>
            </button>
        </div>
    </div>
</section>

<section class="mb-5 flex justify-end">
    <button type="button"
            data-modal-open="?action=create&fragment=1"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-md bg-khaki-500 text-white shadow-sm hover:bg-khaki-600 hover:scale-[1.03] active:scale-[0.98] transition-all duration-200 ease-soft">
        <i class="fa-solid fa-plus text-xs"></i>
        Nouvelle annonce
    </button>
</section>

<section class="catalogue-list" id="catalogue-liste" data-view-mode="cartouche">
    <?php foreach ($catalogue['annonces'] as $annonce): ?>
        <?php $this->partial('_annonce_card', ['annonce' => $annonce]); ?>
    <?php endforeach; ?>

    <?php if ($catalogue['annonces'] === []): ?>
        <div class="col-span-full rounded-lg border border-dashed border-beige-300 bg-white p-10 text-center text-stone-500">
            <p class="text-sm">Aucune annonce pour le moment.</p>
        </div>
    <?php endif; ?>
</section>
