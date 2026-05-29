<?php
    $current = $current ?? 'index';
?>

<header class="sticky top-0 z-30 bg-white/85 backdrop-blur border-b border-beige-200">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 h-16">
        <a href="?action=index" class="flex items-center gap-2 text-stone-900 font-bold tracking-tight">
            <span class="items-center">
                <img src="assets/images/poo-immo.png" alt="Logo Poo Immo" class="h-12 w-12 object-contain">
            </span>
        </a>

        <nav class="flex items-center gap-1 sm:gap-2" aria-label="Navigation principale">
            <?php if ($current !== 'index'): ?>
                <a href="?action=index"
                   class="px-3 py-2 text-sm font-medium text-stone-600 rounded-md hover:bg-beige-100 hover:text-stone-900 transition-colors duration-200 ease-soft">
                    Liste des annonces
                </a>
            <?php endif; ?>

            <?php if ($current !== 'biens'): ?>
                <a href="?action=biens"
                   class="px-3 py-2 text-sm font-medium text-stone-600 rounded-md hover:bg-beige-100 hover:text-stone-900 transition-colors duration-200 ease-soft">
                    Biens sans annonce
                </a>
            <?php endif; ?>

            <?php if ($current !== 'export'): ?>
                <a href="?action=export"
                   class="px-3 py-2 text-sm font-medium text-stone-600 rounded-md hover:bg-beige-100 hover:text-stone-900 transition-colors duration-200 ease-soft">
                    Exporter les données
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>
