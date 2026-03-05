<?php
use App\Core\View;
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <h1 class="mb-4">Admin Login</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= View::e($error) ?>
            </div>
        <?php endif; ?>
        <form method="post" action="/login" novalidate>
            <?= View::csrfField() ?>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= View::e($email ?? '') ?>"
                    required
                    autocomplete="username"
                >
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                    autocomplete="current-password"
                >
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>
