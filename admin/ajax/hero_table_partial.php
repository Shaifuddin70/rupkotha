<?php
// hero_table_partial.php
// This file is included by hero-slider.php for both initial load and AJAX requests.
// It assumes $heroItems, $totalPages, $page, $limit, $products, $totalHeroItems are already defined.
?>
<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Image</th>
        <th>Title</th>
        <th>Subtitle</th>
        <th>Product</th>
        <th>Status</th>
        <th style="width: 180px;">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($heroItems)): ?>
        <tr>
            <td colspan="6" class="text-center">No hero slider items found.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($heroItems as $item): ?>
            <tr>
                <td>
                    <?php if ($item['image']): ?>
                        <img src="../admin/assets/uploads/<?= htmlspecialchars($item['image']) ?>" width="120" alt="Slider Image">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars($item['subtitle']) ?></td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                <td>
                    <form method="post" class="d-inline" name="delete-form"> <!-- Added name="delete-form" -->
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button name="delete" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['id'] ?>">Edit</button>
                </td>
            </tr>

            <!-- Modal for Edit -->
            <div class="modal fade" id="editModal<?= $item['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $item['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?= $item['id'] ?>">Edit Slider Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body row g-3">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <div class="col-md-6">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select" required>
                                        <?php foreach ($products as $p): // $products is available from hero-slider.php ?>
                                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $item['product_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($item['title']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($item['subtitle']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Image (optional)</label>
                                    <input type="file" name="image" class="form-control">
                                    <?php if ($item['image']): ?>
                                        <small class="text-muted">Current: <img src="../admin/assets/uploads/<?= htmlspecialchars($item['image']) ?>" width="50" class="img-thumbnail mt-1"></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="active<?= $item['id'] ?>" <?= $item['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="active<?= $item['id'] ?>">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="update" class="btn btn-success">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        Showing <?= min($offset + 1, $totalHeroItems) ?> to <?= min($offset + $limit, $totalHeroItems) ?> of <?= $totalHeroItems ?> entries
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination mb-0">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
