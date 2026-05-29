<?php

declare(strict_types=1);

namespace App\Http;

/* *
 * Représente une requête HTTP entrante, encapsulant les données de la requête
 * et fournissant des méthodes pour y accéder.
 */
final class Request
{
    private function __construct(
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isFragment(): bool
    {
        if (($this->query['fragment'] ?? null) === '1') {
            return true;
        }

        $requestedWith = $this->server['HTTP_X_REQUESTED_WITH'] ?? '';

        return is_string($requestedWith) && strtolower($requestedWith) === 'fetch';
    }

    public function query(string $key, ?string $default = null): ?string
    {
        return $this->stringValue($this->query[$key] ?? null, $default);
    }

    public function queryInt(string $key): ?int
    {
        $value = $this->query($key);
        if ($value === null || !ctype_digit($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }

    public function postAll(): array
    {
        return $this->post;
    }

    /**
     * @return string[]
     */
    public function queryArray(string $key): array
    {
        $value = $this->query[$key] ?? null;
        if (!is_array($value)) {
            return [];
        }

        $values = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $values[] = (string) $item;
            }
        }

        return $values;
    }

    private function stringValue(mixed $value, ?string $default): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return $default;
    }
}
