<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title ?? 'Poo Immo') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        beige: {
                            50:  '#faf7f2',
                            100: '#f4efe5',
                            200: '#e8dfcc',
                            300: '#d6c8a8',
                        },
                        khaki: {
                            400: '#8a9a6b',
                            500: '#6b7a4a',
                            600: '#566239',
                            700: '#3f4828',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
                    },
                    transitionTimingFunction: {
                        soft: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="assets/js/modal.js" defer></script>
    <script src="assets/js/view-toggle.js" defer></script>
    <script src="assets/js/recherche.js" defer></script>
    <script src="assets/js/multi-picker.js" defer></script>
    <script src="assets/js/export-preview.js" defer></script>
</head>
    <body class="min-h-screen bg-beige-50 text-stone-800 font-sans antialiased">
    <?php $current = $currentAction ?? 'index'; ?>

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
                        Annonces
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
                        Export
                    </a>
                <?php endif; ?>

            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <?= $content ?>
    </main>

    <div id="modal-root" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-hidden="true">
        <div data-modal-backdrop class="absolute inset-0 bg-stone-900/50 backdrop-blur-sm opacity-0 transition-opacity duration-200 ease-soft"></div>
        <div data-modal-panel class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-2xl ring-1 ring-stone-200 opacity-0 scale-95 transition-all duration-200 ease-soft">
            <button type="button" data-modal-close
                    class="absolute top-3 right-3 inline-flex h-9 w-9 items-center justify-center rounded-md text-stone-500 hover:bg-beige-100 hover:text-stone-900 transition-colors">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
            <div data-modal-content class="p-5 sm:p-7"></div>
        </div>
    </div>
    </body>
</html>
