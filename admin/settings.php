<?php
require_once 'includes/header.php';

// Fetch settings (only one row expected)
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
?>

    <h2 class="page-title">Company Settings</h2>

    <div class="card p-4 mb-4">
        <h4 class="mb-3">Current Company Information</h4>
        <?php if ($settings): ?>
            <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Company Name:</strong> <?= htmlspecialchars($settings['company_name']) ?></li>
                <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($settings['phone']) ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($settings['email']) ?></li>
                <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($settings['address']) ?></li>
                <li class="list-group-item"><strong>Facebook:</strong> <?= htmlspecialchars($settings['facebook']) ?></li>
                <li class="list-group-item"><strong>Instagram:</strong> <?= htmlspecialchars($settings['instagram']) ?></li>
                <li class="list-group-item"><strong>Twitter:</strong> <?= htmlspecialchars($settings['twitter']) ?></li>
                <li class="list-group-item">
                    <strong>Logo:</strong><br>
                    <?php if (!empty($settings['logo'])): ?>
                        <img src="../admin/assets/uploads/<?= $settings['logo'] ?>" alt="Logo" height="80">
                    <?php else: ?>
                        <span class="text-muted">No logo uploaded</span>
                    <?php endif; ?>
                </li>
            </ul>
        <?php else: ?>
            <div class="alert alert-warning">No company settings found.</div>
        <?php endif; ?>
    </div>

    <div class="card p-4">
        <h4 class="mb-3">Edit Company Information</h4>
        <form method="post" enctype="multipart/form-data">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?= $settings['company_name'] ?? '' ?>">
                </div>
                <div class="col-md-6">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= $settings['phone'] ?? '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $settings['email'] ?? '' ?>">
                </div>
                <div class="col-md-6">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= $settings['address'] ?? '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Facebook</label>
                    <input type="text" name="facebook" class="form-control" value="<?= $settings['facebook'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label>Instagram</label>
                    <input type="text" name="instagram" class="form-control" value="<?= $settings['instagram'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label>Twitter</label>
                    <input type="text" name="twitter" class="form-control" value="<?= $settings['twitter'] ?? '' ?>">
                </div>
            </div>

            <div class="mb-3">
                <label>Company Logo (optional)</label>
                <input type="file" name="logo" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update Settings</button>
        </form>
    </div>

<?php
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $facebook = trim($_POST['facebook']);
    $instagram = trim($_POST['instagram']);
    $twitter = trim($_POST['twitter']);

    $logo = $settings['logo'] ?? '';
    if (!empty($_FILES['logo']['name'])) {
        $logo = uniqid() . '_' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], '../admin/assets/uploads/' . $logo);
    }

    if ($settings) {
        $stmt = $pdo->prepare("UPDATE settings SET company_name=?, phone=?, email=?, address=?, facebook=?, instagram=?, twitter=?, logo=?");
        $stmt->execute([$company_name, $phone, $email, $address, $facebook, $instagram, $twitter, $logo]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (company_name, phone, email, address, facebook, instagram, twitter, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$company_name, $phone, $email, $address, $facebook, $instagram, $twitter, $logo]);
    }

    header("Location: settings");
    exit;
}
?>

<?php require_once 'includes/footer.php'; ?>