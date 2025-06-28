<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/header.php'; // Assumed to contain $pdo connection

// --- Pagination Configuration ---
$limit = 5; // Number of items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- Handle add/update/delete ---
// This part remains largely the same, but after these operations,
// we should redirect back to the hero-slider.php, potentially with the current page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['update']) || isset($_POST['delete'])) {
        // Handle image upload for add/update
        if ((isset($_POST['add']) || isset($_POST['update'])) && !empty($_FILES['image']['name'])) {
            $image = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_dir = '../admin/assets/uploads/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
        } else {
            $image = null; // No new image uploaded
        }

        // ADD
        if (isset($_POST['add'])) {
            $title = trim($_POST['title']);
            $subtitle = trim($_POST['subtitle']);
            $product_id = intval($_POST['product_id']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO hero_products (product_id, title, subtitle, image, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $title, $subtitle, $image, $is_active]);
        }

        // UPDATE
        if (isset($_POST['update'])) {
            $id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $subtitle = trim($_POST['subtitle']);
            $product_id = intval($_POST['product_id']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($image) { // If a new image was uploaded
                $stmt = $pdo->prepare("UPDATE hero_products SET product_id=?, title=?, subtitle=?, image=?, is_active=? WHERE id=?");
                $stmt->execute([$product_id, $title, $subtitle, $image, $is_active, $id]);
            } else { // No new image, keep existing one
                $stmt = $pdo->prepare("UPDATE hero_products SET product_id=?, title=?, subtitle=?, is_active=? WHERE id=?");
                $stmt->execute([$product_id, $title, $subtitle, $is_active, $id]);
            }
        }

        // DELETE
        if (isset($_POST['delete'])) {
            $id = intval($_POST['id']);
            // Optional: Delete the image file from the server if it exists
            $stmt = $pdo->prepare("SELECT image FROM hero_products WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item && $item['image']) {
                $image_path = '../admin/assets/uploads/' . $item['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            $pdo->prepare("DELETE FROM hero_products WHERE id=?")->execute([$id]);
        }

        // Redirect to prevent form resubmission and update the list
        // We redirect back to the current page to maintain pagination state
        header("Location: hero-slider.php?page=" . $page);
        exit;
    }
}


// --- Fetch Products for Select Dropdown (always needed) ---
$products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch Hero Products for Display (with pagination) ---
// Count total records for pagination
$totalHeroItemsStmt = $pdo->query("SELECT COUNT(*) FROM hero_products");
$totalHeroItems = $totalHeroItemsStmt->fetchColumn();
$totalPages = ceil($totalHeroItems / $limit);

// Fetch paginated hero products
$heroItemsStmt = $pdo->prepare("SELECT h.*, p.name AS product_name
                                FROM hero_products h
                                JOIN products p ON h.product_id = p.id
                                ORDER BY h.id DESC
                                LIMIT :limit OFFSET :offset");
$heroItemsStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$heroItemsStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$heroItemsStmt->execute();
$heroItems = $heroItemsStmt->fetchAll(PDO::FETCH_ASSOC);

// If this is an AJAX request, only return the table rows and pagination
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    include 'ajax/hero_table_partial.php'; // Include the partial view for AJAX
    exit;
}
?>

<h2>Hero Slider Management</h2>

<div class="card p-4 mb-3">
    <form method="post" enctype="multipart/form-data" class="mb-4">
        <h4>Add New Slider Item</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" checked>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
        </div>
        <button class="btn btn-success mt-3" name="add">Add Slider Item</button>
    </form>
</div>

<div class="card p-4">
    <div id="hero-slider-table-container">
        <?php include 'ajax/hero_table_partial.php'; // Initial load of table data ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Include Bootstrap 5 JS and Popper.js (if not already in header.php) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableContainer = document.getElementById('hero-slider-table-container');

        function loadPage(pageNumber) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `hero-slider.php?page=${pageNumber}`, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Indicate AJAX request

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    tableContainer.innerHTML = xhr.responseText;
                    // Re-initialize Bootstrap modals for newly loaded content
                    const newModals = tableContainer.querySelectorAll('.modal');
                    newModals.forEach(modalElement => {
                        new bootstrap.Modal(modalElement);
                    });
                } else {
                    console.error('Error loading page:', xhr.status, xhr.statusText);
                }
            };

            xhr.onerror = function() {
                console.error('Network error while loading page.');
            };

            xhr.send();
        }

        // Event delegation for pagination links
        tableContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('page-link')) {
                event.preventDefault(); // Prevent default link behavior
                const pageHref = event.target.getAttribute('href');
                const urlParams = new URLSearchParams(pageHref.split('?')[1]);
                const newPage = urlParams.get('page');
                if (newPage) {
                    loadPage(newPage);
                }
            }
        });

        // Delete confirmation using JavaScript (instead of browser's confirm)
        // This is a basic example; for a more polished UI, consider a custom modal.
        tableContainer.addEventListener('submit', function(event) {
            if (event.target.name === 'delete-form') { // Give your delete form a name like 'delete-form'
                event.preventDefault(); // Stop default form submission
                if (confirm('Are you sure you want to delete this slider item?')) {
                    event.target.submit(); // Submit the form if confirmed
                }
            }
        });
    });
</script>
