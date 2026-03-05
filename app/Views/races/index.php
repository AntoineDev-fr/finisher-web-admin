<?php
use App\Core\View;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <h1 class="m-0">Races</h1>
    <a href="/admin/races/create" class="btn btn-success">Create Race</a>
</div>

<div class="mb-3">
    <label for="race-search" class="form-label">Search (name or contact email)</label>
    <input
        id="race-search"
        type="search"
        class="form-control"
        placeholder="Type to search..."
        autocomplete="off"
        data-search-url="/admin/races/search"
    >
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th scope="col">Photo</th>
                <th scope="col">Name</th>
                <th scope="col">Date</th>
                <th scope="col">Price</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody id="race-table-body">
            <?php require __DIR__ . '/_table.php'; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.initRaceSearch) {
        window.initRaceSearch('race-search', 'race-table-body');
    }
});
</script>
