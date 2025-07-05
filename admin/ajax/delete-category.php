<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid category ID']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
if ($stmt->execute([$id])) {
    echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully', 'id' => $id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete category']);
}
