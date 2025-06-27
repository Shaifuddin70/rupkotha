<?php

require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    echo 'success';
    exit;
}
echo 'fail';
