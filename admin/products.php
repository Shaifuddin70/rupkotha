<?php
require_once 'includes/header.php';

// --- PAGINATION SETUP ---
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$per_page = 10; // Number of products to display per page
$offset = ($page - 1) * $per_page;

// Get total number of products for pagination calculation
$total_products = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn();
$total_pages = ceil($total_products / $per_page);
// --- END PAGINATION SETUP ---


// Initialize messages from session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);


// It's highly recommended to move this function to a central 'includes/functions.php' file
function handleProductImageUpload(array $file, ?string $current_image = null): string|false
{
    // No new file uploaded, return current image.
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $current_image;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "File upload error code: " . $file['error'];
        return false;
    }

    $upload_dir = 'assets/uploads/';
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    // Size and Type validation
    if ($file['size'] > $max_file_size) {
        $_SESSION['error_message'] = "File size exceeds the maximum limit of 5MB.";
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mime_types, true)) {
        $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
        return false;
    }

    // Generate a unique and secure filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_file_name = bin2hex(random_bytes(12)) . '.' . $extension;
    $destination = $upload_dir . $new_file_name;

    // Ensure upload directory exists and is writable
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        $_SESSION['error_message'] = "Failed to create upload directory.";
        return false;
    }


    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Delete old image if a new one is successfully uploaded
        if ($current_image && file_exists($upload_dir . $current_image)) {
            unlink($upload_dir . $current_image);
        }
        return $new_file_name;
    }

    $_SESSION['error_message'] = "Failed to move uploaded file.";
    return false;
}


// Add/Update product logic remains the same
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['name']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $description = trim($_POST['description']);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if (empty($name) || $price === false || $category_id === false || $stock === false) {
        $_SESSION['error_message'] = "Invalid input provided.";
    } else {
        $stmt = $pdo->prepare("SELECT id, stock, image FROM products WHERE name = ? AND category_id = ?");
        $stmt->execute([$name, $category_id]);
        $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);

        $new_image_name = handleProductImageUpload($_FILES['image'], $existing_product['image'] ?? null);

        if ($new_image_name !== false) {
            if ($existing_product) {
                // Update existing product
                $new_stock = $existing_product['stock'] + $stock;
                $stmt = $pdo->prepare("UPDATE products SET stock = ?, description = ?, image = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$new_stock, $description, $new_image_name, $existing_product['id']])) {
                    $_SESSION['success_message'] = "Product '" . htmlspecialchars($name) . "' updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to update product.";
                }
            } else {
                // Insert new product
                $stmt = $pdo->prepare("INSERT INTO products (name, price, category_id, image, description, stock) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $price, $category_id, $new_image_name, $description, $stock])) {
                    $_SESSION['success_message'] = "Product '" . htmlspecialchars($name) . "' added successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to add new product.";
                }
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- MODIFIED PRODUCT QUERY WITH PAGINATION ---
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name
                       FROM products p
                       JOIN categories c ON p.category_id = c.id
                       ORDER BY p.id DESC
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <h2 class="page-title">Manage Products</h2>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


    <div class="card mb-4 p-4">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-4">
                <label for="product_name" class="form-label">Name</label>
                <input type="text" name="name" id="product_name" required class="form-control">
            </div>
            <div class="col-md-3">
                <label for="product_description" class="form-label">Description</label>
                <input type="text" name="description" id="product_description" required class="form-control">
            </div>
            <div class="col-md-2">
                <label for="product_price" class="form-label">Price</label>
                <input type="number" name="price" id="product_price" required class="form-control" step="0.01" min="0">
            </div>
            <div class="col-md-3">
                <label for="product_category" class="form-label">Category</label>
                <select name="category_id" id="product_category" required class="form-select">
                    <option value="" disabled selected>-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="product_image" class="form-label">Image</label>
                <input type="file" name="image" id="product_image" class="form-control">
                <small class="form-text text-muted">Max 5MB. JPG, PNG, GIF, WEBP.</small>
            </div>
            <div class="col-md-2">
                <label for="product_stock" class="form-label">Stock</label>
                <input type="number" name="stock" id="product_stock" required class="form-control" min="0" value="0">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Add Product</button>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <h4 class="mb-3">Product List</h4>
            <table id="productsTable" class="table table-bordered table-hover align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price (৳)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <tr id="product-row-<?= htmlspecialchars($prod['id']) ?>">
                            <td>
                                <?= htmlspecialchars($prod['id']) ?>
                            </td>
                            <td>
                                <img src="assets/uploads/<?= htmlspecialchars($prod['image'] ?? 'default.png') ?>"
                                     alt="<?= htmlspecialchars($prod['name']) ?>" width="60" class="img-thumbnail">
                            </td>
                            <td>
                                <?= htmlspecialchars($prod['name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($prod['description']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($prod['category_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(number_format($prod['price'], 2)) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($prod['stock']) ?>
                            </td>
                            <td>
                                <a href="edit-product.php?id=<?= htmlspecialchars($prod['id']) ?>"
                                   class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger delete-product-btn"
                                        data-id="<?= htmlspecialchars($prod['id']) ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productsTable = document.getElementById('productsTable');
            if (productsTable) {
                productsTable.addEventListener('click', function(e) {
                    if (e.target && e.target.classList.contains('delete-product-btn')) {
                        const button = e.target;
                        const productId = button.dataset.id;
                        const productName = button.closest('tr').querySelector('td:nth-child(3)').textContent;

                        if (confirm(`Are you sure you want to delete "${productName}"?`)) {
                            fetch('ajax/delete-product.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'id=' + encodeURIComponent(productId)
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        document.getElementById(`product-row-${productId}`).remove();
                                        alert(data.message);
                                    } else {
                                        alert('Failed to delete product: ' + data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred. Please check the console.');
                                });
                        }
                    }
                });
            }
        });
    </script>

<?php include 'includes/footer.php'; ?>