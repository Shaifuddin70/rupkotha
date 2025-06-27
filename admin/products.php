<?php
require_once 'includes/header.php';

// Add product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $stock = intval($_POST['stock']);
    $image = '';

    // Check for existing product with same name + category
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name = ? AND category_id = ?");
    $stmt->execute([$name, $category_id]);
    $existing = $stmt->fetch();

    // Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "assets/uploads/";
        $image = uniqid() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
    }

    $now = date('Y-m-d H:i:s');

    if ($existing) {
        // If exists, update the stock
        $newStock = $existing['stock'] + $stock;

        // Optional: update image/description if provided
        $updateStmt = $pdo->prepare("UPDATE products SET stock = ?, description = ?, updated_at = ?" . ($image ? ", image = ?" : "") . " WHERE id = ?");
        $params = [$newStock, $description, $now];
        if ($image) $params[] = $image;
        $params[] = $existing['id'];
        $updateStmt->execute($params);
    } else {
        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (name, price, category_id, image, description, stock, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $category_id, $image, $description, $stock, $now]);
    }

    header("Location: products");
    exit;


}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Fetch all products
$products = $pdo->query("SELECT products.*, categories.name AS category_name
                         FROM products
                         JOIN categories ON products.category_id = categories.id
                         ORDER BY products.id DESC")->fetchAll();
?>

<h2 class="page-title">Manage Products</h2>

<!-- Add Product Form -->
<div class="card mb-4">
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-4">
            <label>Name</label>
            <input type="text" name="name" required class="form-control">
        </div>
        <div class="col-md-2">
            <label>Price</label>
            <input type="number" name="price" required class="form-control" step="0.01">
        </div>
        <div class="col-md-3">
            <label>Category</label>
            <select name="category_id" required class="form-select">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>

        <div class="col-md-4">
            <label>Description</label>
            <input type="text" name="description" required class="form-control">
        </div>

        <div class="col-md-2">
            <label>Stock</label>
            <label>
                <input type="number" name="stock" required class="form-control" min="0" value="0">
            </label>
        </div>

        <div class="col-12">
            <button class="btn btn-success">Add Product</button>
        </div>
    </form>
 </div>

    <!-- Product Table -->
<div class="card">
    <h4>Product List</h4>
    <table class="table table-bordered table-hover mt-3 align-middle">
        <thead class="table">
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>Price (৳)</th>
            <th>Stock</th>
            <th>Created</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $prod): ?>
            <tr id="product-<?= $prod['id'] ?>">
                <td><?= $prod['id'] ?></td>
                <td>
                    <?php if ($prod['image']): ?>
                        <img src="assets/uploads/<?= $prod['image'] ?>" alt="" width="60">
                    <?php else: ?>
                        <span class="text-muted">No image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($prod['name']) ?></td>
                <td><?= htmlspecialchars($prod['description']) ?></td>
                <td><?= htmlspecialchars($prod['category_name']) ?></td>
                <td><?= number_format($prod['price'], 2) ?></td>
                <td><?= number_format($prod['stock']) ?></td>
                <td><?= date('Y-m-d', strtotime($prod['created_at'])) ?></td>
                <td><?= date('Y-m-d', strtotime($prod['updated_at'])) ?></td>
                <td>
                    <a href="edit-product?id=<?= $prod['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <button class="btn btn-sm btn-danger delete-product" data-id="<?= $prod['id'] ?>">Delete</button>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    document.querySelectorAll('.delete-product').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            if (confirm("Are you sure you want to delete this product?")) {
                fetch('ajax/delete-product', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(id)
                })
                    .then(res => res.text())
                    .then(response => {
                        if (response === 'success') {
                            const row = document.getElementById('product-' + id);
                            if (row) row.remove();
                        } else {
                            alert("Failed to delete product.");
                        }
                    });
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
