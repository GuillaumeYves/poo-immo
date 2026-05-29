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
    <?php $this->partial('_navbar', [
        'current' => $currentAction ?? 'index',
    ]); ?>

    <main class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <?= $content ?>
    </main>

    <?php $this->partial('_modal'); ?>
    </body>
</html>
