<?php include 'includes/header.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$heroProducts = $pdo->query("SELECT * FROM hero_products WHERE is_active = 1 ORDER BY id DESC LIMIT 3");
$heroProducts = $heroProducts ? $heroProducts->fetchAll() : [];

?>


<!-- Hero Section with Slider -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($heroProducts as $index => $item): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" style="background: url('assets/uploads/<?= $item['image'] ?>') center/cover no-repeat; height: 400px; position: relative;">
                <div class="overlay position-absolute w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5);"></div>
                <div class="container position-relative z-1 text-white text-center d-flex flex-column justify-content-center align-items-center h-100">
                    <h2 class="display-5 fw-bold"><?= htmlspecialchars($item['title']) ?></h2>
                    <p class="lead"><?= htmlspecialchars($item['subtitle']) ?></p>
                    <a href="product?id=<?= $item['product_id'] ?>" class="btn btn-warning mt-3">View Product</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<?php include 'includes/footer.php'; ?>
