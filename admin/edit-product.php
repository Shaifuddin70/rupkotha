<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='alert alert-danger'>Product not found.</div>";
    include 'includes/footer.php';
    exit;
}

// Fetch categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $price       = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $stock       = intval($_POST['stock']);

    // Handle image
    $image = $product['image']; // keep old image
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "assets/uploads/";
        $imageName = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $targetPath = $target_dir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
            // Optionally remove old image
            if ($product['image'] && file_exists($target_dir . $product['image'])) {
                unlink($target_dir . $product['image']);
            }
            $image = $imageName;
        }
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, category_id = ?, image = ?, description = ?, stock = ?, updated_at = ? WHERE id = ?");
    $stmt->execute([$name, $price, $category_id, $image, $description, $stock, $now, $id]);

    header("Location: products");
    exit;
}
?>

<h2 class="page-title">Edit Product</h2>

<form method="post" enctype="multipart/form-data" class="admin-card p-3">
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>">
    </div>

    <div class="mb-3">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?= $product['price'] ?>">
    </div>

    <div class="mb-3">
        <label>Category</label>
        <select name="category_id" class="form-select" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Stock</label>
        <input type="number" name="stock" min="0" class="form-control" value="<?= $product['stock'] ?>">
    </div>

    <div class="mb-3">
        <label>Current Image:</label><br>
        <?php if ($product['image']): ?>
            <img src="assets/uploads/<?= $product['image'] ?>" width="120" alt="Product Image">
        <?php else: ?>
            <p class="text-muted">No image uploaded.</p>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label>Upload New Image (optional)</label>
        <input type="file" name="image" class="form-control">
    </div>

    <button class="btn btn-primary">Update Product</button>
    <a href="products" class="btn btn-secondary">Cancel</a>
</form>

<?php include 'includes/footer.php'; ?>
