<?php
// admin/orders.php

require_once 'includes/header.php';
require_once 'includes/functions.php';

// --- Handle Order Status Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = trim($_POST['status']);

    // A list of allowed statuses to prevent arbitrary values
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

    if ($order_id && in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Order #{$order_id} status updated to {$status}."];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to update order status.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid order ID or status.'];
    }
    // Redirect to the same page to show the message and prevent resubmission
    // Appending existing filters to the redirect URL
    $query_string = http_build_query($_GET);
    redirect('orders?' . $query_string);
}


// --- Filtering & Pagination Logic ---
$search_id = trim($_GET['search_id'] ?? '');
$filter_status = trim($_GET['filter_status'] ?? 'all');

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build the WHERE clause for the query based on filters
$where_clauses = [];
$params = [];

if (!empty($search_id)) {
    $where_clauses[] = "o.id = ?";
    $params[] = $search_id;
}
if ($filter_status !== 'all') {
    $where_clauses[] = "o.status = ?";
    $params[] = $filter_status;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Get total count for pagination
$total_orders_stmt = $pdo->prepare("SELECT COUNT(o.id) FROM orders o" . $where_sql);
$total_orders_stmt->execute($params);
$total_orders = $total_orders_stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Fetch the orders for the current page
$sql = "SELECT o.*, u.username 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id"
    . $where_sql .
    " ORDER BY o.created_at DESC 
        LIMIT {$per_page} OFFSET {$offset}";

$orders_stmt = $pdo->prepare($sql);
$orders_stmt->execute($params);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="page-title">Manage Orders</h2>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">Filter Orders</div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search_id" class="form-label">Search by Order ID</label>
                <input type="text" name="search_id" id="search_id" class="form-control" value="<?= esc_html($search_id) ?>" placeholder="e.g., 123">
            </div>
            <div class="col-md-4">
                <label for="filter_status" class="form-label">Filter by Status</label>
                <select name="filter_status" id="filter_status" class="form-select">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Processing" <?= $filter_status === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $filter_status === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $filter_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="orders" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        All Orders (Showing <?= count($orders) ?> of <?= $total_orders ?>)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead >
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No orders found matching your criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="fw-bold"><?= esc_html($order['id']) ?></td>
                            <td><?= esc_html($order['username'] ?? 'Guest') ?></td>
                            <td><?= format_date($order['created_at']) ?></td>
                            <td><?= formatPrice($order['total_amount']) ?></td>
                            <td><?= esc_html($order['payment_method']) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-success">Save</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="admin-order-details?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    // Preserve filter query parameters in pagination links
                    $filter_params = http_build_query(['search_id' => $search_id, 'filter_status' => $filter_status]);
                    ?>
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $filter_params ?>"><span>&laquo;</span></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= $filter_params ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $filter_params ?>"><span>&raquo;</span></a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
