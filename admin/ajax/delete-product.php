<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('unauthorized');
}

require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Delete image if exists
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product && $product['image']) {
        $file = "../../uploads/" . $product['image'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleted = $stmt->execute([$id]);

    echo $deleted ? 'success' : 'error';
    exit;
}
