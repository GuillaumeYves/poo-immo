<?php

declare(strict_types=1);

namespace App\Http;

/* *
 * Représente une réponse HTTP sortante, encapsulant le contenu, le statut et les en-têtes
 * de la réponse, et fournissant des méthodes pour rendre des réponses.
 */
final class Response
{
    public function __construct(
        private readonly string $content = '',
        private readonly int $status = 200,
        private readonly array $headers = [],
    ) {
    }

    public static function redirect(string $location): self
    {
        return new self('', 302, ['Location' => $location]);
    }

    public static function json(array $payload, int $status = 200): self
    {
        return new self(
            (string) json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
