<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }

    public static function redirect(string $path, int $status = 302): void
    {
        header('Location: ' . $path, true, $status);
        exit;
    }

    public static function abort(int $status, string $message): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Error</title></head><body><h1>' . $safe . '</h1></body></html>';
        exit;
    }
}
