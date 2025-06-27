<?php
include 'includes/header.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        redirect('index.php');
    } else {
        $error = "Invalid email or password!";
    }
}
?>


<h2>Login</h2>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label>Email</label>
        <label>
            <input type="email" name="email" required class="form-control">
        </label>
    </div>
    <div class="mb-3">
        <label>Password</label>
        <label>
            <input type="password" name="password" required class="form-control">
        </label>
    </div>
    <button type="submit" class="btn btn-success">Login</button>
</form>
<?php include 'includes/footer.php'; ?>
