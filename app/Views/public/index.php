<?php
use App\Core\View;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="m-0">All Races</h1>
        <p class="text-muted mb-0">Explore upcoming races and trail events.</p>
    </div>
    <a href="/login" class="btn btn-outline-dark">Admin Login</a>
</div>

<?php if (empty($races)): ?>
    <div class="alert alert-info" role="status">No races available yet.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($races as $race): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= View::e(View::apiImageUrl($race['photo_path'])) ?>" class="card-img-top" alt="Photo <?= View::e($race['nom']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h5 card-title"><?= View::e($race['nom']) ?></h2>
                        <p class="card-text text-muted mb-1"><?= View::e($race['date_event']) ?></p>
                        <p class="card-text">Price: <?= View::e($race['prix']) ?></p>
                        <div class="mt-auto">
                            <a href="/races/<?= View::e($race['id']) ?>" class="btn btn-primary">View details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
