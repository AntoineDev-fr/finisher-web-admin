<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    private static string $apiPublicUrl = '';

    public static function setApiPublicUrl(string $url): void
    {
        self::$apiPublicUrl = rtrim($url, '/');
    }

    public static function render(string $view, array $data = [], bool $layout = true): string
    {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_file($viewPath)) {
            return '';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (!$layout) {
            return $content;
        }

        $layoutPath = __DIR__ . '/../Views/layout.php';
        ob_start();
        require $layoutPath;
        return ob_get_clean();
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function apiImageUrl(string $path): string
    {
        $path = ltrim($path, '/');
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (self::$apiPublicUrl === '') {
            return '/' . $path;
        }
        return self::$apiPublicUrl . '/' . $path;
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::e(Csrf::token()) . '">';
    }

    public static function flash(string $key): ?string
    {
        if (!isset($_SESSION[$key])) {
            return null;
        }
        $value = (string)$_SESSION[$key];
        unset($_SESSION[$key]);
        return $value;
    }

    public static function setFlash(string $key, string $value): void
    {
        $_SESSION[$key] = $value;
    }
}
