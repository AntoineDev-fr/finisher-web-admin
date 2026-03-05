<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['admin_user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['admin_user'] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION['admin_user'] = [
            'id' => (int)$user['id'],
            'email' => (string)$user['email'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_user']);
    }

    public static function handle(Request $request): bool
    {
        if (!self::check()) {
            Response::redirect('/login');
            return false;
        }
        return true;
    }
}
