<?php
use App\Core\View;

$errors = $errors ?? [];
$old = $old ?? [];
?>
<h1 class="mb-4">Edit Race</h1>

<form method="post" action="/admin/races/<?= View::e($race['id']) ?>/update" enctype="multipart/form-data" novalidate>
    <?= View::csrfField() ?>

    <div class="row g-3">
        <div class="col-12">
            <label for="nom" class="form-label">Name</label>
            <input type="text" id="nom" name="nom" class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['nom'] ?? '') ?>" aria-describedby="nom-error" required>
            <?php if (isset($errors['nom'])): ?>
                <div id="nom-error" class="invalid-feedback"><?= View::e($errors['nom']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" rows="4" aria-describedby="description-error" required><?= View::e($old['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div id="description-error" class="invalid-feedback"><?= View::e($errors['description']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="date_event" class="form-label">Date</label>
            <input type="datetime-local" id="date_event" name="date_event" class="form-control <?= isset($errors['date_event']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['date_event'] ?? '') ?>" aria-describedby="date-event-error" required>
            <?php if (isset($errors['date_event'])): ?>
                <div id="date-event-error" class="invalid-feedback"><?= View::e($errors['date_event']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="prix" class="form-label">Price</label>
            <input type="number" id="prix" name="prix" class="form-control <?= isset($errors['prix']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['prix'] ?? '') ?>" aria-describedby="prix-error" min="0" required>
            <?php if (isset($errors['prix'])): ?>
                <div id="prix-error" class="invalid-feedback"><?= View::e($errors['prix']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" id="latitude" name="latitude" class="form-control <?= isset($errors['latitude']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['latitude'] ?? '') ?>" aria-describedby="latitude-error" required>
            <?php if (isset($errors['latitude'])): ?>
                <div id="latitude-error" class="invalid-feedback"><?= View::e($errors['latitude']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" id="longitude" name="longitude" class="form-control <?= isset($errors['longitude']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['longitude'] ?? '') ?>" aria-describedby="longitude-error" required>
            <?php if (isset($errors['longitude'])): ?>
                <div id="longitude-error" class="invalid-feedback"><?= View::e($errors['longitude']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <div id="race-map" class="map" aria-label="Map"></div>
            <p class="form-text">Click on the map to set latitude and longitude.</p>
        </div>

        <div class="col-md-6">
            <label for="contact_nom" class="form-label">Contact Name</label>
            <input type="text" id="contact_nom" name="contact_nom" class="form-control <?= isset($errors['contact_nom']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['contact_nom'] ?? '') ?>" aria-describedby="contact-nom-error" required>
            <?php if (isset($errors['contact_nom'])): ?>
                <div id="contact-nom-error" class="invalid-feedback"><?= View::e($errors['contact_nom']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="contact_email" class="form-label">Contact Email</label>
            <input type="email" id="contact_email" name="contact_email" class="form-control <?= isset($errors['contact_email']) ? 'is-invalid' : '' ?>"
                value="<?= View::e($old['contact_email'] ?? '') ?>" aria-describedby="contact-email-error" required>
            <?php if (isset($errors['contact_email'])): ?>
                <div id="contact-email-error" class="invalid-feedback"><?= View::e($errors['contact_email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label for="photo" class="form-label">Photo (optional)</label>
            <div class="mb-2">
                <img src="<?= View::e(View::apiImageUrl($race['photo_path'])) ?>" alt="Photo <?= View::e($race['nom']) ?>" class="thumb">
            </div>
            <input type="file" id="photo" name="photo" class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>"
                accept=".jpg,.jpeg,.png,.webp" aria-describedby="photo-error">
            <?php if (isset($errors['photo'])): ?>
                <div id="photo-error" class="invalid-feedback"><?= View::e($errors['photo']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/admin/races/<?= View::e($race['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.initRaceMap) {
        window.initRaceMap('race-map', 'latitude', 'longitude');
    }
});
</script>
