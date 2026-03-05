<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public string $method = 'GET';
    public string $path = '/';
    public array $query = [];
    public array $post = [];
    public array $files = [];
    public array $params = [];

    public static function capture(): self
    {
        $request = new self();
        $request->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        $request->path = $path === '' ? '/' : $path;
        $request->query = $_GET ?? [];

        if ($request->method === 'POST') {
            $request->post = $_POST ?? [];
            $request->files = $_FILES ?? [];
        }

        return $request;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function queryParam(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function postParam(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }
}
