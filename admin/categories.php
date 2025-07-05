
<?php
require_once 'includes/header.php';

$success_message = '';
$error_message = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $error_message = "Category name cannot be empty.";
    } else {
        // Check if category already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = "Category '" . htmlspecialchars($name) . "' already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            if ($stmt->execute([$name])) {
                $success_message = "Category '" . htmlspecialchars($name) . "' added successfully!";
            } else {
                $error_message = "Failed to add category '" . htmlspecialchars($name) . "'.";
            }
        }
    }
}

// Fetch categories for display
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="page-title">Manage Categories</h2>

<?php if ($success_message): ?>
    <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($success_message) ?>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<div class="card mb-4 p-4">
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label for="category_name" class="form-label">Category Name</label>
            <input type="text" name="name" id="category_name" required class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="col-12">
            <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <h4>Category List</h4>
    <table class="table table-bordered table-hover mt-3 align-middle table-responsive">
        <!-- Toast Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div id="toastMessage" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="toastBody">Success</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <thead class="table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr id="category-<?= htmlspecialchars($cat['id']) ?>">
                <td><?= htmlspecialchars($cat['id']) ?></td>
                <td>
                    <span class="category-name-text" data-id="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></span>
                    <input type="text" class="form-control d-none category-name-input" value="<?= htmlspecialchars($cat['name']) ?>" data-id="<?= $cat['id'] ?>">
                </td>

                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($cat['created_at']))) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($cat['updated_at']))) ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning edit-category-btn"
                            data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                            data-id="<?= htmlspecialchars($cat['id']) ?>">Edit</button>
                    <button class="btn btn-sm btn-danger delete-category-btn" data-id="<?= htmlspecialchars($cat['id']) ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editCategoryModalBody">
                Loading category details...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="editCategoryForm" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = new bootstrap.Toast(document.getElementById('toastMessage'));

        function showToast(message, type = 'primary') {
            const toastEl = document.getElementById('toastMessage');
            toastEl.className = 'toast align-items-center text-bg-' + type + ' border-0';
            document.getElementById('toastBody').textContent = message;
            toast.show();
        }

        // Inline edit activation
        document.querySelectorAll('.category-name-text').forEach(span => {
            span.addEventListener('click', function () {
                const id = this.dataset.id;
                const input = document.querySelector(`.category-name-input[data-id='${id}']`);
                this.classList.add('d-none');
                input.classList.remove('d-none');
                input.focus();
            });
        });

        // Inline edit submission
        document.querySelectorAll('.category-name-input').forEach(input => {
            input.addEventListener('blur', function () {
                const id = this.dataset.id;
                const newName = this.value.trim();

                fetch('ajax/update-category.php', {
                    method: 'POST',
                    body: new URLSearchParams({ id, name: newName })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            document.querySelector(`.category-name-text[data-id='${id}']`).textContent = newName;
                        } else {
                            showToast(data.message, 'danger');
                            this.value = document.querySelector(`.category-name-text[data-id='${id}']`).textContent;
                        }
                        this.classList.add('d-none');
                        document.querySelector(`.category-name-text[data-id='${id}']`).classList.remove('d-none');
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        showToast('Update failed!', 'danger');
                        this.classList.add('d-none');
                        document.querySelector(`.category-name-text[data-id='${id}']`).classList.remove('d-none');
                    });
            });
        });

        // AJAX Delete
        document.querySelectorAll('.delete-category-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                if (!confirm("Delete this category?")) return;

                fetch('ajax/delete-category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${encodeURIComponent(id)}`
                })
                    .then(res => res.text())
                    .then(res => {
                        if (res.trim() === 'success') {
                            document.getElementById(`category-${id}`).remove();
                            showToast('Category deleted successfully!', 'success');
                        } else {
                            showToast('Delete failed!', 'danger');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('Error deleting category.', 'danger');
                    });
            });
        });
    });
</script>

