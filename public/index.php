<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/../app/Core/Env.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

use App\Core\Env;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Controllers\AuthController;
use App\Controllers\RaceController;
use App\Controllers\PublicController;

Env::load(__DIR__ . '/../.env');

$config = require __DIR__ . '/../config/config.php';
Database::init($config['db']);
View::setApiPublicUrl($config['api']['public_url'] ?? '');

set_exception_handler(function (Throwable $e): void {
    Response::abort(500, 'An unexpected error occurred.');
});

$request = Request::capture();
$router = new Router();

$authController = new AuthController($config);
$raceController = new RaceController($config);
$publicController = new PublicController();

$csrfMiddleware = [Csrf::class, 'handle'];
$authMiddleware = [Auth::class, 'handle'];

$router->add('GET', '/login', [$authController, 'showLogin']);
$router->add('POST', '/login', [$authController, 'login'], [$csrfMiddleware]);
$router->add('POST', '/logout', [$authController, 'logout'], [$csrfMiddleware]);

$router->add('GET', '/', [$publicController, 'index']);
$router->add('GET', '/races', [$publicController, 'index']);
$router->add('GET', '/races/{id}', [$publicController, 'show']);

$router->add('GET', '/admin/races', [$raceController, 'index'], [$authMiddleware]);
$router->add('GET', '/admin/races/search', [$raceController, 'search'], [$authMiddleware]);
$router->add('GET', '/admin/races/create', [$raceController, 'create'], [$authMiddleware]);
$router->add('POST', '/admin/races', [$raceController, 'store'], [$authMiddleware, $csrfMiddleware]);
$router->add('GET', '/admin/races/{id}', [$raceController, 'show'], [$authMiddleware]);
$router->add('GET', '/admin/races/{id}/edit', [$raceController, 'edit'], [$authMiddleware]);
$router->add('POST', '/admin/races/{id}/update', [$raceController, 'update'], [$authMiddleware, $csrfMiddleware]);
$router->add('POST', '/admin/races/{id}/delete', [$raceController, 'delete'], [$authMiddleware, $csrfMiddleware]);
$router->add('GET', '/admin/races/{id}/pdf', [$raceController, 'pdf'], [$authMiddleware]);

$router->dispatch($request);
