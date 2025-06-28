<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM hero_products WHERE id=?");
        echo $stmt->execute([$id]) ? 'success' : 'fail';
        exit;
    }

    if ($_POST['action'] === 'update') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $subtitle = trim($_POST['subtitle']);
        $product_id = intval($_POST['product_id']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($_FILES['image']['name'])) {
            $image = uniqid() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../assets/uploads/' . $image);
            $stmt = $pdo->prepare("UPDATE hero_products SET product_id=?, title=?, subtitle=?, image=?, is_active=? WHERE id=?");
            $result = $stmt->execute([$product_id, $title, $subtitle, $image, $is_active, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE hero_products SET product_id=?, title=?, subtitle=?, is_active=? WHERE id=?");
            $result = $stmt->execute([$product_id, $title, $subtitle, $is_active, $id]);
        }

        echo $result ? 'success' : 'fail';
        exit;
    }
}
?>
