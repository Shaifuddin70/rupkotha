<?php
// This is the customer's order history page, e.g., orders

// STEP 1: Start session and include necessary files FIRST.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

// STEP 2: Authentication Check. Redirect if not logged in.
if (!isLoggedIn()) {
    redirect('login?redirect=orders');
}

$user_id = $_SESSION['user_id'];

// --- DATA FETCHING ---
// Fetch all orders belonging to the current user.
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);


// STEP 3: Now, include the header.
include 'includes/header.php';
?>

<div class="page-header" style="background-color: #f8f9fa; padding: 2rem 0; margin-bottom: 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index">Home</a></li>
                <li class="breadcrumb-item"><a href="profile">My Account</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Orders</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold mt-2">My Orders</h1>
    </div>
</div>

<main class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0">Order History</h5>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="text-center p-4">
                    <p class="text-muted">You have not placed any orders yet.</p>
                    <a href="index" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-bold">#<?= esc_html($order['id']) ?></td>
                                <td><?= format_date($order['created_at']) ?></td>
                                <td><?= formatPrice($order['total_amount']) ?></td>
                                <td>
                                        <span class="badge
                                            <?php
                                        switch ($order['status']) {
                                            case 'Completed': echo 'bg-success'; break;
                                            case 'Pending': echo 'bg-warning text-dark'; break;
                                            case 'Cancelled': echo 'bg-danger'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                            <?= esc_html($order['status']) ?>
                                        </span>
                                </td>
                                <td class="text-end">
                                    <a href="order-details?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye-fill me-1"></i>View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
