<?php

session_start();


require_once '../../includes/db.php'; // Path relative to admin/ajax/


if (!isset($_SESSION['admin_id'])) {
    http_response_code(403); // Forbidden
    echo '<div class="alert alert-danger">Access Denied. Please log in.</div>';
    exit;
}

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id === 0) {
    echo '<div class="alert alert-danger">Invalid category ID provided.</div>';
    exit;
}

// Fetch category details
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    echo '<div class="alert alert-danger">Category not found.</div>';
    exit;
}

// Output the HTML form (no full page HTML, just the form itself)
?>
<form id="editCategoryForm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
    <div class="mb-3">
        <label for="edit_category_name" class="form-label">Category Name</label>
        <input type="text" name="name" id="edit_category_name" class="form-control"
               value="<?= htmlspecialchars($category['name']) ?>" required>
    </div>
</form>