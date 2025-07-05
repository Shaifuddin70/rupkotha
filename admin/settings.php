<?php
require_once 'includes/header.php';
require_once 'includes/functions.php'; // Using functions like esc_html()

// --- Initial Data Fetch ---
// Fetch the single row of settings from the database.
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all incoming data
    $company_name = trim($_POST['company_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');

    // New fields for shipping and payment
    $shipping_fee_dhaka = filter_input(INPUT_POST, 'shipping_fee_dhaka', FILTER_VALIDATE_FLOAT);
    $shipping_fee_outside = filter_input(INPUT_POST, 'shipping_fee_outside', FILTER_VALIDATE_FLOAT);
    $bkash_number = trim($_POST['bkash_number'] ?? '');
    $nagad_number = trim($_POST['nagad_number'] ?? '');
    $rocket_number = trim($_POST['rocket_number'] ?? '');

    $logo = $settings['logo'] ?? ''; // Keep the existing logo by default

    // Use the robust handleImageUpload function for the logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $new_logo = handleImageUpload($_FILES['logo'], $logo);
        if ($new_logo !== false) {
            $logo = $new_logo;
        }
        // Error message will be set in the session by the function if it fails
    }

    // Prepare data for database execution
    $params = [
        $company_name, $phone, $email, $address,
        $facebook, $instagram, $twitter, $logo,
        $shipping_fee_dhaka, $shipping_fee_outside,
        $bkash_number, $nagad_number, $rocket_number
    ];

    if ($settings) {
        // Update existing settings
        $sql = "UPDATE settings SET 
                    company_name=?, phone=?, email=?, address=?, 
                    facebook=?, instagram=?, twitter=?, logo=?, 
                    shipping_fee_dhaka=?, shipping_fee_outside=?, 
                    bkash_number=?, nagad_number=?, rocket_number=?, 
                    updated_at=CURRENT_TIMESTAMP 
                WHERE id = ?";
        $params[] = $settings['id'];
    } else {
        // Insert new settings if none exist
        $sql = "INSERT INTO settings (
                    company_name, phone, email, address, 
                    facebook, instagram, twitter, logo, 
                    shipping_fee_dhaka, shipping_fee_outside, 
                    bkash_number, nagad_number, rocket_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Settings updated successfully!'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to update settings.'];
    }

    redirect('settings');
}
?>

<h2 class="page-title">Store Settings</h2>
<p class="text-muted">Manage your store's general information, shipping fees, and payment numbers from here.</p>

<div class="card p-4">
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3 ">
            <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
        </div>
        <div class="row">
            <!-- Left Column: General & Social -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>General Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" name="company_name" id="company_name" class="form-control" value="<?= esc_html($settings['company_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="<?= esc_html($settings['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?= esc_html($settings['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" name="address" id="address" class="form-control" value="<?= esc_html($settings['address'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Social Media Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="facebook" class="form-label">Facebook URL</label>
                                <input type="text" name="facebook" id="facebook" class="form-control" value="<?= esc_html($settings['facebook'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="instagram" class="form-label">Instagram URL</label>
                                <input type="text" name="instagram" id="instagram" class="form-control" value="<?= esc_html($settings['instagram'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="twitter" class="form-label">Twitter URL</label>
                                <input type="text" name="twitter" id="twitter" class="form-control" value="<?= esc_html($settings['twitter'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Company Logo</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($settings['logo']) && file_exists('assets/uploads/' . $settings['logo'])): ?>
                            <img src="assets/uploads/<?= esc_html($settings['logo']) ?>" alt="Current Logo" class="img-fluid rounded mb-3" style="max-height: 100px;">
                        <?php endif; ?>
                        <input type="file" name="logo" id="logo" class="form-control">
                        <small class="form-text text-muted">Upload a new logo to replace the current one.</small>
                    </div>
                </div>
            </div>

            <!-- Right Column: Shipping, Payment & Logo -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Shipping Fees</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_fee_dhaka" class="form-label">Inside Dhaka (৳)</label>
                            <input type="number" step="0.01" name="shipping_fee_dhaka" id="shipping_fee_dhaka" class="form-control" value="<?= esc_html($settings['shipping_fee_dhaka'] ?? '60.00') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="shipping_fee_outside" class="form-label">Outside Dhaka (৳)</label>
                            <input type="number" step="0.01" name="shipping_fee_outside" id="shipping_fee_outside" class="form-control" value="<?= esc_html($settings['shipping_fee_outside'] ?? '120.00') ?>">
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Mobile Payment Numbers</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="bkash_number" class="form-label">bKash Number</label>
                            <input type="text" name="bkash_number" id="bkash_number" class="form-control" value="<?= esc_html($settings['bkash_number'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nagad_number" class="form-label">Nagad Number</label>
                            <input type="text" name="nagad_number" id="nagad_number" class="form-control" value="<?= esc_html($settings['nagad_number'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="rocket_number" class="form-label">Rocket Number</label>
                            <input type="text" name="rocket_number" id="rocket_number" class="form-control" value="<?= esc_html($settings['rocket_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
