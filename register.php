<?php include 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $phone, $password]);

    redirect('login.php');
}
?>


<h2>Register</h2>
<form method="post">
    <div class="mb-3">
        <label>Username</label>
        <label>
            <input type="text" name="username" required class="form-control">
        </label>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <label>
            <input type="email" name="email" required class="form-control">
        </label>
    </div>
    <div class="mb-3">
        <label>Phone</label>
        <label>
            <input type="text" name="phone" required class="form-control">
        </label>
    </div>
    <div class="mb-3">
        <label>Password</label>
        <label>
            <input type="password" name="password" required class="form-control">
        </label>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
<?php include 'includes/footer.php'; ?>
