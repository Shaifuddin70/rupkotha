<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// --- PAGINATION SETUP ---
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$per_page = 10;
$offset = ($page - 1) * $per_page;

$total_products = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// --- FORM HANDLING for Add/Update Product ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Sanitize and validate inputs
    $name = trim($_POST['name']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $description = trim($_POST['description']);

    $stock_input = trim($_POST['stock'] ?? '');
    $stock = null;
    if (is_numeric($stock_input) && intval($stock_input) >= 0) {
        $stock = intval($stock_input); // This correctly converts "05" to 5
    }

    // Main validation
    if (empty($name) || $price === false || $category_id === false || $stock === null) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid input provided. Please check all fields.'];
    } else {
        // Check if a product with the same name and category already exists
        $stmt = $pdo->prepare("SELECT id, stock, image FROM products WHERE name = ? AND category_id = ?");
        $stmt->execute([$name, $category_id]);
        $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_product) {
            // --- UPDATE EXISTING PRODUCT ---
            // Use the existing image as a fallback if no new one is uploaded
            $image_to_save = handleImageUpload($_FILES['image'], $existing_product['image']);

            if ($image_to_save !== false) {
                $new_stock = $existing_product['stock'] + $stock; // Add to existing stock

                $update_stmt = $pdo->prepare(
                    "UPDATE products SET price = ?, description = ?, stock = ?, image = ?, updated_at = NOW() WHERE id = ?"
                );

                if ($update_stmt->execute([$price, $description, $new_stock, $image_to_save, $existing_product['id']])) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Product '" . esc_html($name) . "' was updated successfully."];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to update the existing product.'];
                }
            }
            // Error message for a failed image upload is handled by handleImageUpload()

        } else {
            // --- INSERT NEW PRODUCT ---
            $image_name = handleImageUpload($_FILES['image']);
            if ($image_name !== false) {
                $insert_stmt = $pdo->prepare("INSERT INTO products (name, price, category_id, image, description, stock) VALUES (?, ?, ?, ?, ?, ?)");
                if ($insert_stmt->execute([$name, $price, $category_id, $image_name, $description, $stock])) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Product added successfully!'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to add new product.'];
                }
            }
            // Error message for a failed image upload is handled by handleImageUpload()
        }
    }
    redirect('products');
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch products for the table
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

<div class="card mb-4 p-4">
    <h4 >Add Product</h4>
     <form method="post" enctype="multipart/form-data" class="row g-3 ">
        <div class="col-md-4">
            <label for="product_name" class="form-label">Name</label>
            <input type="text" name="name" id="product_name" required class="form-control">
        </div>
        <div class="col-md-4">
            <label for="product_description" class="form-label">Description</label>
            <input type="text" name="description" id="product_description" required class="form-control">
        </div>
        <div class="col-md-4">
            <label for="product_category" class="form-label">Category</label>
            <select name="category_id" id="product_category" required class="form-select">
                <option value="" disabled selected>-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= esc_html($cat['id']) ?>"><?= esc_html($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="product_price" class="form-label">Price</label>
            <input type="number" name="price" id="product_price" required class="form-control" step="0.01" min="0">
        </div>
        <div class="col-md-4">
            <label for="product_stock" class="form-label">Stock (Add Quantity)</label>
            <input type="number" name="stock" id="product_stock" required class="form-control" min="0" value="0">
        </div>
        <div class="col-md-4">
            <label for="product_image" class="form-label">Image</label>
            <input type="file" name="image" id="product_image" class="form-control">
            <small class="form-text text-muted">Uploading a new image will replace the old one if the product exists.</small>
        </div>
        <div class="col-12">
            <button type="submit" name="add_product" class="btn btn-success">Add / Update Product</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <h4 class="mb-3">Product List</h4>
    <div class="table-responsive">
        <table id="productsTable" class="table table-bordered table-hover align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $prod): ?>
                <tr id="product-row-<?= $prod['id'] ?>">
                    <td><?= esc_html($prod['id']) ?></td>
                    <td><img src="assets/uploads/<?= esc_html($prod['image'] ?? 'default.png') ?>" alt="<?= esc_html($prod['name']) ?>" width="60" class="img-thumbnail"></td>
                    <td><?= esc_html($prod['name']) ?></td>
                    <td><?= esc_html($prod['category_name']) ?></td>
                    <td><?= formatPrice($prod['price']) ?></td>
                    <td><?= esc_html($prod['stock']) ?></td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle" type="checkbox" role="switch" data-id="<?= $prod['id'] ?>" <?= $prod['is_active'] ? 'checked' : '' ?>>
                            <span class="badge <?= $prod['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $prod['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                    </td>
                    <td>
                        <a href="edit-product.php?id=<?= htmlspecialchars($prod['id']) ?>"

                           class="btn btn-sm btn-warning">Edit</a>

                        <button type="button" class="btn btn-sm btn-danger delete-product-btn"

                                data-id="<?= htmlspecialchars($prod['id']) ?>">Delete</button> </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">«</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">»</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productsTable = document.getElementById('productsTable');

        productsTable.addEventListener('click', function(e) {
            // Handle Delete Button
            if (e.target.closest('.delete-product-btn')) {
                const button = e.target.closest('.delete-product-btn');
                const productId = button.dataset.id;

                if (confirm('Are you sure you want to try deleting this product? It will fail if the product has existing orders.')) {
                    fetch('ajax/delete-product', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + encodeURIComponent(productId)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                document.getElementById(`product-row-${productId}`).remove();
                                alert(data.message);
                            } else {
                                alert(`Error: ${data.message}`);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            }
        });

        // Handle Status Toggle Switch
        productsTable.addEventListener('change', function(e) {
            if (e.target.classList.contains('status-toggle')) {
                const toggle = e.target;
                const productId = toggle.dataset.id;
                const newStatus = toggle.checked ? 1 : 0;

                fetch('ajax/toggle_product_status', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${productId}&status=${newStatus}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const badge = toggle.nextElementSibling;
                            badge.textContent = newStatus ? 'Active' : 'Inactive';
                            badge.classList.toggle('bg-success', newStatus === 1);
                            badge.classList.toggle('bg-secondary', newStatus === 0);
                        } else {
                            alert(`Error: ${data.message}`);
                            toggle.checked = !toggle.checked;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
