<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// --- Handle Add Category with Post/Redirect/Get Pattern ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Category name cannot be empty.'];
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Category '" . esc_html($name) . "' already exists."];
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            if ($stmt->execute([$name])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Category added successfully!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to add category.'];
            }
        }
    }
    // Redirect to the same page to prevent form resubmission
    redirect('categories');
}

// Fetch categories for display
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="page-title">Manage Categories</h2>

<div class="card mb-4 p-4">
    <h4 class="mb-3">Add New Category</h4>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label for="category_name" class="form-label">Category Name</label>
            <input type="text" name="name" id="category_name" required class="form-control">
        </div>
        <div class="col-12">
            <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <h4 class="mb-3">Category List</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr id="category-row-<?= $cat['id'] ?>">
                    <td><?= esc_html($cat['id']) ?></td>
                    <td class="category-name-cell" data-id="<?= $cat['id'] ?>"><?= esc_html($cat['name']) ?></td>
                    <td><?= format_date($cat['created_at']) ?></td>
                    <td class="category-updated-cell" data-id="<?= $cat['id'] ?>"><?= format_date($cat['updated_at']) ?></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-warning edit-category-btn" data-id="<?= $cat['id'] ?>">
                            <i class="bi bi-pencil-fill"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-category-btn" data-id="<?= $cat['id'] ?>">
                            <i class="bi bi-trash-fill"></i> Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm">
                <div class="modal-body" id="editCategoryModalBody">
                    <!-- AJAX content will be loaded here -->
                    <div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editCategoryModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const modalBody = document.getElementById('editCategoryModalBody');
        const editForm = document.getElementById('editCategoryForm');

        // --- Handle Edit Button Click ---
        document.querySelectorAll('.edit-category-btn').forEach(button => {
            button.addEventListener('click', function () {
                const categoryId = this.dataset.id;
                modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                editModal.show();

                // Fetch the form content via AJAX
                fetch(`ajax/fetch-category-form?id=${categoryId}`)
                    .then(response => response.text())
                    .then(html => {
                        modalBody.innerHTML = html;
                    })
                    .catch(error => {
                        modalBody.innerHTML = '<div class="alert alert-danger">Failed to load category details.</div>';
                        console.error('Error:', error);
                    });
            });
        });

        // --- Handle Edit Form Submission ---
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('ajax/update-category', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update the table row with new data
                        document.querySelector(`.category-name-cell[data-id='${data.id}']`).textContent = data.name;
                        document.querySelector(`.category-updated-cell[data-id='${data.id}']`).textContent = data.updated_at;
                        editModal.hide();
                        // You can add a success toast notification here if you have one
                        alert('Category updated successfully!');
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the category.');
                });
        });

        // --- Handle Delete Button Click ---
        document.querySelectorAll('.delete-category-btn').forEach(button => {
            button.addEventListener('click', function () {
                const categoryId = this.dataset.id;
                if (!confirm("Are you sure you want to delete this category? This cannot be undone.")) return;

                fetch('ajax/delete-category', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${encodeURIComponent(categoryId)}`
                })
                    .then(response => response.json()) // Expect a JSON response now
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById(`category-row-${categoryId}`).remove();
                            alert(data.message);
                        } else {
                            alert(`Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the category.');
                    });
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
