<?php
// This is your single product detail page, e.g., product.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// The header file already starts the session and includes db.php and functions.php
include 'includes/header.php';

// --- DATA FETCHING FOR THE PRODUCT PAGE ---

// 1. Get Product ID from URL and validate it
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    // If no valid ID, show an error or redirect
    echo "<div class='container my-5'><div class='alert alert-danger'>Invalid product ID.</div></div>";
    include 'includes/footer.php';
    exit;
}

// 2. Fetch the main product details
$product_stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name 
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.id = :id"
);
$product_stmt->execute([':id' => $product_id]);
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

// Handle case where product is not found
if (!$product) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Product not found.</div></div>";
    include 'includes/footer.php';
    exit;
}


// 3. Fetch related products from the same category
$related_products_stmt = $pdo->prepare(
    "SELECT * FROM products 
     WHERE category_id = :category_id AND id != :product_id
     ORDER BY RAND()
     LIMIT 4"
);
$related_products_stmt->execute([
    ':category_id' => $product['category_id'],
    ':product_id' => $product_id
]);
$related_products = $related_products_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Custom CSS for this page -->
<style>

</style>

<main class="container my-5">
    <div class="row">
        <!-- Product Image Column -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <img src="admin/assets/uploads/<?= esc_html($product['image']) ?>"
                 alt="<?= esc_html($product['name']) ?>"
                 class="product-image-main shadow-sm">
        </div>

        <!-- Product Details Column -->
        <div class="col-lg-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="category.php?id=<?= $product['category_id'] ?>"><?= esc_html($product['category_name']) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc_html($product['name']) ?></li>
                </ol>
            </nav>

            <h1 class="display-5 fw-bold"><?= esc_html($product['name']) ?></h1>

            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-primary me-2"><?= esc_html($product['category_name']) ?></span>
                <span class="text-muted">Stock: <strong> <?= $product['stock'] > 0 ? esc_html($product['stock']) . ' available' : 'Out of Stock' ?></strong></span>
            </div>

            <p class="product-price mb-4"><?= formatPrice($product['price']) ?></p>

            <div class="product-description mb-4">
                <h5 class="fw-bold">Description</h5>
                <p class="text-secondary"><?= nl2br(esc_html($product['description'])) ?></p>
            </div>

            <form action="add_to_cart.php" method="post">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="d-flex align-items-center">
                    <div class="me-3 d-flex align-items-center">
                        <label for="quantity" class="form-label fw-bold me-2">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" class="form-control quantity-input text-center" value="1" min="1" max="<?= $product['stock'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1 " <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-cart-plus-fill me-2"></i>
                        <?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($related_products)): ?>
        <section class="related-products mt-5 pt-5">
            <h2 class="section-title">Related Products</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($related_products as $related_product): ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <a href="product.php?id=<?= $related_product['id'] ?>">
                                <img src="admin/assets/uploads/<?= esc_html($related_product['image']) ?>" class="card-img-top product-card-img-top" alt="<?= esc_html($related_product['name']) ?>">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title h6"><a href="product.php?id=<?= $related_product['id'] ?>" class="text-dark text-decoration-none"><?= esc_html($related_product['name']) ?></a></h5>
                                <p class="card-text fw-bold text-primary mb-0"><?= formatPrice($related_product['price']) ?></p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <a href="add_to_cart.php?id=<?= $related_product['id'] ?>" class="btn btn-outline-primary w-100">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
