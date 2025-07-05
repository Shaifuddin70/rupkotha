<?php
// admin/admin-order-details.php

require_once 'includes/header.php';
require_once 'includes/functions.php';

// --- Authentication Check ---
if (!isset($_SESSION['admin_id'])) {
    redirect('login');
}

// --- Get Order ID and Validate ---
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid order ID provided.'];
    redirect('orders');
}

// --- Handle Order Status Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = trim($_POST['status']);
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

    if (in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Order #{$order_id} status updated to {$status}."];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to update order status.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid status selected.'];
    }
    redirect('admin-order-details?id=' . $order_id);
}


// --- Data Fetching ---
// 1. Fetch the main order details, joining with users table to get customer info.
$order_stmt = $pdo->prepare(
    "SELECT o.*, u.username, u.email, u.phone, u.address 
     FROM orders o
     LEFT JOIN users u ON o.user_id = u.id
     WHERE o.id = :order_id"
);
$order_stmt->execute([':order_id' => $order_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

// If order doesn't exist, redirect back to the list.
if (!$order) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Order not found.'];
    redirect('orders');
}

// 2. Fetch the items associated with this order.
$order_items_stmt = $pdo->prepare(
    "SELECT oi.quantity, oi.price, p.name, p.image 
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = :order_id"
);
$order_items_stmt->execute([':order_id' => $order_id]);
$order_items = $order_items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal from items.
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>

<!-- FINAL, corrected Print-Specific CSS -->
<style>
    @media print {
        /* Hide all elements by default */
        body * {
            visibility: hidden;
        }

        /* Make only the invoice area and its children visible */
        .invoice-area, .invoice-area * {
            visibility: visible;
        }

        /* Position the invoice at the top of the page */
        .invoice-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* General print resets */
        body {
            margin: 0;
            padding: 0;
        }

        .invoice-area .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
            margin: 0;
        }

        /* Ensure text is black for better printer readability */
        .invoice-area * {
            color: #000 !important;
        }

        /* Help Bootstrap badges print their background colors */
        .badge {
            -webkit-print-color-adjust: exact; /* Chrome, Safari */
            color-adjust: exact; /* Firefox */
            border: 1px solid #6c757d;
        }
    }
</style>

<div class="no-print">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">Order Details</h2>
        <div>
            <a href="orders" class="btn btn-secondary">Back to Orders</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer-fill me-1"></i> Print Invoice</button>
        </div>
    </div>
</div>

<div class="invoice-area">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Invoice for Order #<?= esc_html($order['id']) ?></h5>
            <span class="badge fs-6 <?=
            match($order['status']) {
                'Completed' => 'bg-success',
                'Pending' => 'bg-warning text-dark',
                'Processing' => 'bg-info text-dark',
                'Shipped' => 'bg-primary',
                'Cancelled' => 'bg-danger',
                default => 'bg-secondary'
            }
            ?>"><?= esc_html($order['status']) ?></span>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Customer Details</h6>
                    <p class="mb-1"><strong>Name:</strong> <?= esc_html($order['username']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= esc_html($order['email']) ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?= esc_html($order['phone']) ?></p>
                    <p class="mb-0"><strong>Shipping Address:</strong> <?= esc_html($order['address']) ?></p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <h6>Payment Details</h6>
                    <p class="mb-1"><strong>Method:</strong> <?= esc_html($order['payment_method']) ?></p>
                    <?php if($order['payment_method'] !== 'cod' && !empty($order['payment_sender_no'])): ?>
                        <p class="mb-1"><strong>Sender No:</strong> <?= esc_html($order['payment_sender_no']) ?></p>
                        <p class="mb-0"><strong>TrxID:</strong> <?= esc_html($order['payment_trx_id']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <h5 class="mb-3">Items Ordered</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead  >
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?= esc_html($item['name']) ?></td>
                            <td class="text-end"><?= formatPrice($item['price']) ?></td>
                            <td class="text-center"><?= esc_html($item['quantity']) ?></td>
                            <td class="text-end fw-bold"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="fw-bold">
                    <tr>
                        <td colspan="3" class="text-end border-0">Subtotal</td>
                        <td class="text-end border-0"><?= formatPrice($subtotal) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end border-0">Shipping Fee</td>
                        <td class="text-end border-0"><?= formatPrice($order['shipping_fee']) ?></td>
                    </tr>
                    <tr class=" fs-5">
                        <td colspan="3" class="text-end">Grand Total</td>
                        <td class="text-end"><?= formatPrice($order['total_amount']) ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 no-print">
    <div class="card-body">
        <h5 class="card-title">Update Order Status</h5>
        <form method="post" class="d-flex gap-2">
            <select name="status" class="form-select" style="max-width: 250px;">
                <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
