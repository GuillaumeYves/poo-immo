<?php

declare(strict_types=1);

namespace App\View;

use RuntimeException;

/**
 * Responsable du rendu des vues.
 */
final class ViewRenderer
{
    private const PARTIALS_PREFIX = 'partials/';

    public function __construct(
        private readonly string $basePath,
    ) {
    }

    public function render(string $template, array $params = [], ?string $layout = 'layout'): string
    {
        $content = $this->renderFile($template, $params);

        if ($layout === null) {
            return $content;
        }

        return $this->renderFile($layout, [
            ...$params,
            'content' => $content,
        ]);
    }

    public function partial(string $template, array $params = []): void
    {
        if (!str_starts_with($template, self::PARTIALS_PREFIX)) {
            $template = self::PARTIALS_PREFIX . ltrim($template, '/');
        }

        echo $this->render($template, $params, null);
    }

    public function e(string|int|float|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function renderFile(string $template, array $params): string
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . $template . '.php';
        if (!is_file($path)) {
            throw new RuntimeException("Vue introuvable : {$template}");
        }

        extract($params, EXTR_SKIP);

        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}
