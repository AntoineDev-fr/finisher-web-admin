<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Auth;
use App\Models\AdminUser;

final class AuthController
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/admin/races');
        }

        $html = View::render('auth/login', [
            'title' => 'Admin Login',
            'error' => null,
            'email' => '',
        ]);
        Response::html($html);
    }

    public function login(Request $request): void
    {
        $email = trim((string)$request->postParam('email', ''));
        $password = (string)$request->postParam('password', '');

        $user = AdminUser::findByEmail($email);
        $valid = $user !== null && password_verify($password, $user['password_hash']);

        if (!$valid) {
            $html = View::render('auth/login', [
                'title' => 'Admin Login',
                'error' => 'Invalid credentials.',
                'email' => $email,
            ]);
            Response::html($html, 401);
        }

        Auth::login($user);
        Response::redirect('/admin/races');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
