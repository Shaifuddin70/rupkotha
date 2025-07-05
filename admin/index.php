<?php
include 'includes/header.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login");
    exit;
}

 ?>
<p class="card">This is your admin dashboard overview.</p>
<?php include 'includes/footer.php'; ?>
