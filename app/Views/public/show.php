<?php
use App\Core\View;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="m-0">Race Details</h1>
    <a href="/races" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <img src="<?= View::e(View::apiImageUrl($race['photo_path'])) ?>" alt="Photo <?= View::e($race['nom']) ?>" class="img-fluid rounded">
    </div>
    <div class="col-12 col-lg-7">
        <table class="table table-bordered">
            <tr><th scope="row">Name</th><td><?= View::e($race['nom']) ?></td></tr>
            <tr><th scope="row">Description</th><td><?= View::e($race['description']) ?></td></tr>
            <tr><th scope="row">Date</th><td><?= View::e($race['date_event']) ?></td></tr>
            <tr><th scope="row">Price</th><td><?= View::e($race['prix']) ?></td></tr>
            <tr><th scope="row">Latitude</th><td><?= View::e($race['latitude']) ?></td></tr>
            <tr><th scope="row">Longitude</th><td><?= View::e($race['longitude']) ?></td></tr>
            <tr><th scope="row">Contact Name</th><td><?= View::e($race['contact_nom']) ?></td></tr>
            <tr><th scope="row">Contact Email</th><td><?= View::e($race['contact_email']) ?></td></tr>
        </table>
    </div>
</div>
