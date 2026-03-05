<?php
use App\Core\View;
use App\Core\Auth;

$flashSuccess = View::flash('flash_success');
$flashError = View::flash('flash_error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title ?? 'Finisher Admin') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/races">Finisher</a>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-light btn-sm" href="/races">Public Races</a>
                <?php if (Auth::check()): ?>
                    <a class="btn btn-light btn-sm" href="/admin/races">Admin</a>
                    <span class="text-white small"><?= View::e(Auth::user()['email'] ?? '') ?></span>
                    <form action="/logout" method="post" class="m-0">
                        <?= View::csrfField() ?>
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm" href="/login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main id="main" class="container py-4">
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" role="status">
                <?= View::e($flashSuccess) ?>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-warning" role="alert">
                <?= View::e($flashError) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
