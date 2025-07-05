<?php
require_once 'includes/header.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login");
    exit;
}



// Pagination
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Handle Add, Update, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['update']) || isset($_POST['delete'])) {
        $image = null;

        if ((isset($_POST['add']) || isset($_POST['update'])) && !empty($_FILES['image']['name'])) {
            $image = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_dir = '../admin/assets/uploads/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
        }

        if (isset($_POST['add'])) {
            $stmt = $pdo->prepare("INSERT INTO hero_products (product_id, title, subtitle, image, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                intval($_POST['product_id']),
                trim($_POST['title']),
                trim($_POST['subtitle']),
                $image,
                isset($_POST['is_active']) ? 1 : 0
            ]);
        }

        if (isset($_POST['update'])) {
            $id = intval($_POST['id']);
            $fields = [
                intval($_POST['product_id']),
                trim($_POST['title']),
                trim($_POST['subtitle']),
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ];
            if ($image) {
                $stmt = $pdo->prepare("UPDATE hero_products SET product_id=?, title=?, subtitle=?, image=?, is_active=? WHERE id=?");
                array_splice($fields, 3, 0, [$image]); // insert image before is_active
            } else {
                $stmt = $pdo->prepare("UPDATE hero_products SET product_id=?, title=?, subtitle=?, is_active=? WHERE id=?");
            }
            $stmt->execute($fields);
        }

        if (isset($_POST['delete'])) {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("SELECT image FROM hero_products WHERE id=?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item && $item['image']) {
                $imgPath = '../admin/assets/uploads/' . $item['image'];
                if (file_exists($imgPath)) unlink($imgPath);
            }
            $pdo->prepare("DELETE FROM hero_products WHERE id=?")->execute([$id]);
        }

        header("Location: hero-slider.php?page=" . $page);
        exit;
    }
}

// Fetch dropdown options
$products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Pagination data
$totalHeroItems = $pdo->query("SELECT COUNT(*) FROM hero_products")->fetchColumn();
$totalPages = ceil($totalHeroItems / $limit);

$stmt = $pdo->prepare("
    SELECT h.*, p.name AS product_name
    FROM hero_products h
    JOIN products p ON h.product_id = p.id
    ORDER BY h.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$heroItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    include 'ajax/hero_table_partial.php';
    exit;
}
?>

<h2>Hero Slider Management</h2>

<div class="card p-4 mb-3">
    <form method="post" enctype="multipart/form-data">
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
    <div class="table-responsive" id="hero-slider-table-container">
        <table class="table table-bordered align-middle">

            <tbody id="hero-slider-tbody">
            <?php include 'ajax/hero_table_partial.php'; ?>
            </tbody>
        </table>
        <div id="hero-slider-pagination">
            <!-- Pagination will be rendered inside partial -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('hero-slider-table-container');

        function loadPage(pageNumber) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `hero-slider.php?page=${pageNumber}`, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = xhr.responseText;

                    const newTbody = tempDiv.querySelector('#hero-slider-tbody');
                    const newPagination = tempDiv.querySelector('#hero-slider-pagination');

                    if (newTbody) {
                        document.getElementById('hero-slider-tbody').innerHTML = newTbody.innerHTML;
                    }
                    if (newPagination) {
                        document.getElementById('hero-slider-pagination').innerHTML = newPagination.innerHTML;
                    }

                    const newModals = document.querySelectorAll('.modal');
                    newModals.forEach(modalElement => new bootstrap.Modal(modalElement));
                }
            };
            xhr.send();
        }

        tableContainer.addEventListener('click', function (event) {
            if (event.target.classList.contains('page-link')) {
                event.preventDefault();
                const href = event.target.getAttribute('href');
                const newPage = new URLSearchParams(href.split('?')[1]).get('page');
                if (newPage) loadPage(newPage);
            }
        });

        tableContainer.addEventListener('submit', function (event) {
            if (event.target.name === 'delete-form') {
                event.preventDefault();
                if (confirm('Are you sure you want to delete this slider item?')) {
                    event.target.submit();
                }
            }
        });
    });
</script>
