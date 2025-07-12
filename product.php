<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get product ID from URL and validate it
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    redirect('all-products.php');
}

// --- FETCH MAIN PRODUCT DETAILS ---
$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.id = ? AND p.is_active = 1 AND p.deleted_at IS NULL"
);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    redirect('all-products.php');
}

// --- FETCH ADDITIONAL PRODUCT IMAGES ---
$image_stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY id");
$image_stmt->execute([$product_id]);
$additional_images = $image_stmt->fetchAll(PDO::FETCH_ASSOC);

// Combine the main image with additional images for the gallery
$all_images = array_merge([['image_path' => $product['image']]], $additional_images);

// --- FETCH RELATED PRODUCTS (from the same category) ---
$related_stmt = $pdo->prepare(
    "SELECT * FROM products
     WHERE category_id = ? AND id != ? AND is_active = 1 AND deleted_at IS NULL
     LIMIT 4"
);
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container my-5">
    <div class="row">
        <!-- Image Gallery Column -->
        <div class="col-lg-6">
            <div class="product-gallery">
                <div class="main-image-container mb-3">
                    <img id="main-product-image" src="admin/assets/uploads/<?= esc_html($product['image']) ?>"
                         alt="<?= esc_html($product['name']) ?>" class="img-fluid rounded shadow-sm">
                </div>
                <?php if (count($all_images) > 1): ?>
                    <div class="thumbnail-container">
                        <?php foreach ($all_images as $img): ?>
                            <img src="admin/assets/uploads/<?= esc_html($img['image_path']) ?>"
                                 alt="Thumbnail of <?= esc_html($product['name']) ?>" class="img-thumbnail thumb-image">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Details Column -->
        <div class="col-lg-6">
            <div class="product-details ps-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a
                                    href="category.php?id=<?= $product['category_id'] ?>"><?= esc_html($product['category_name']) ?></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><?= esc_html($product['name']) ?></li>
                    </ol>
                </nav>

                <h1 class="product-title"><?= esc_html($product['name']) ?></h1>

                <div class="d-flex align-items-center mb-3">
                    <span class="price-display me-3"><?= formatPrice($product['price']) ?></span>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle-fill me-1"></i> In Stock</span>
                    <?php else: ?>
                        <span class="badge bg-danger-soft text-danger"><i class="bi bi-x-circle-fill me-1"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>

                <p class="product-short-description"><?= nl2br(esc_html($product['description'])) ?></p>

                <form action="add_to_cart.php" method="POST" class="mt-4">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="d-flex align-items-center">
                        <div class="quantity-selector me-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm quantity-btn"
                                    data-action="decrease">-
                            </button>
                            <input type="number" name="quantity" class="form-control text-center quantity-input"
                                   value="1" min="1"
                                   max="<?= $product['stock'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <button type="button" class="btn btn-outline-secondary btn-sm quantity-btn"
                                    data-action="increase">+
                            </button>
                        </div>
                        <button type="submit"
                                class="btn btn-primary btn-lg flex-grow-1" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus-fill me-2"></i> Add to Cart
                        </button>
                    </div>
                </form>

                <div class="product-meta mt-4">
                    <span>Category: <a
                                href="category.php?id=<?= $product['category_id'] ?>"><?= esc_html($product['category_name']) ?></a></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products mt-5 pt-5 border-top">
            <h2 class="text-center mb-4">You Might Also Like</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($related_products as $related): ?>
                    <div class="col">
                        <div class="card h-100 product-card-sm">
                            <a href="product.php?id=<?= $related['id'] ?>">
                                <img src="admin/assets/uploads/<?= esc_html($related['image']) ?>" class="card-img-top"
                                     alt="<?= esc_html($related['name']) ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><a
                                            href="product.php?id=<?= $related['id'] ?>"><?= esc_html($related['name']) ?></a>
                                </h5>
                                <p class="card-text price"><?= formatPrice($related['price']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mainImage = document.getElementById('main-product-image');
        const thumbnails = document.querySelectorAll('.thumb-image');

        // Set the first thumbnail as active by default
        if (thumbnails.length > 0) {
            thumbnails[0].classList.add('active');
        }

        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function () {
                mainImage.src = this.src;

                // Update active state for thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Quantity selector logic
        const quantityInputs = document.querySelectorAll('.quantity-input');
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function () {
                const action = this.dataset.action;
                const input = this.parentElement.querySelector('.quantity-input');
                let value = parseInt(input.value);
                const max = parseInt(input.max);

                if (action === 'increase' && value < max) {
                    value++;
                } else if (action === 'decrease' && value > 1) {
                    value--;
                }
                input.value = value;
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
