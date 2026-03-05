<?php
use App\Core\View;
?>
<?php if (empty($races)): ?>
    <tr>
        <td colspan="5" class="text-center text-muted">No races found.</td>
    </tr>
<?php else: ?>
    <?php foreach ($races as $race): ?>
        <tr>
            <td>
                <img
                    src="<?= View::e(View::apiImageUrl($race['photo_path'])) ?>"
                    alt="Photo <?= View::e($race['nom']) ?>"
                    class="thumb"
                >
            </td>
            <td><?= View::e($race['nom']) ?></td>
            <td><?= View::e($race['date_event']) ?></td>
            <td><?= View::e($race['prix']) ?></td>
            <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="/admin/races/<?= View::e($race['id']) ?>">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="/admin/races/<?= View::e($race['id']) ?>/edit">Edit</a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
