<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');

if ($id <= 0 || $name === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Category name already exists']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$name, $id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Category updated successfully',
        'id' => $id,
        'name' => htmlspecialchars($name),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
