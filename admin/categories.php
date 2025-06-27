<?php include 'includes/header.php'; ?>

<h2 class="page-title ">Manage Categories</h2>

<!-- Add Category Form (AJAX) -->


        <div class="card  mb-4">
            <form id="category-form" class="p-3">
                <div class="top-section">
        <div class=" d-flex align-items-center">
            <label for="name" class="form-label w-100 mb-0">New Category Name:</label>
            <input type="text" name="name" id="name" required class="form-control " placeholder="e.g. Electronics">
        </div>
        <button type="submit" class="btn btn-success">Add Category</button>
        <div id="cat-response" class="mt-2 text-success"></div>
                </div>
            </form>
        </div>



<!-- Category List -->
<div class="card p-3 ">
    <h4>Category List</h4>
    <table class="table table-bordered mt-3" id="cat-table">
        <thead>
        <tr>
            <th>SL</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
        $count = 1;
        foreach ($categories as $cat): ?>
            <tr id="cat-<?= $cat['id'] ?>">
                <td><?= $count; ?></td>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td><?= $cat['created_at'] ?></td>
                <td>
                    <a href="category-items?id=<?= $cat['id'] ?>" class="btn btn-sm btn-primary">View Items</a>
                    <a href="edit-category?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $cat['id'] ?>">Delete</button>
                </td>
            </tr>

        <?php  $count++; endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);

        fetch('ajax/add-category', {
            method: 'POST',
            body: formData
        }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cat-response').textContent = 'Category added!';
                    form.reset();
                    const table = document.querySelector('#cat-table tbody');
                    table.insertAdjacentHTML('afterbegin', data.newRowHtml);
                } else {
                    document.getElementById('cat-response').textContent = data.message || 'Failed to add category.';

                }
            });
    });

    // Delete category
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            if (confirm("Delete this category?")) {
                fetch('ajax/delete-category', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + id
                }).then(res => res.text())
                    .then(response => {
                        if (response === 'success') {
                            document.getElementById('cat-' + id).remove();
                        } else {
                            alert("Failed to delete.");
                        }
                    });
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
