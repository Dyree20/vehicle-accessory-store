<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
</head>
<body class="auth-bg">
    <div class="vehicle-anim-bg">
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com.svg" class="vehicle1" alt="Animated Car" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (1).svg" class="vehicle2" alt="Animated Car 2" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (2).svg" class="vehicle3" alt="Animated Car 3" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (3).svg" class="vehicle4" alt="Animated Car 4" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com.svg" class="vehicle5" alt="Animated Car 5" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (1).svg" class="vehicle6" alt="Animated Car 6" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (2).svg" class="vehicle7" alt="Animated Car 7" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (3).svg" class="vehicle8" alt="Animated Car 8" />
    </div>
    <a href="/vehicle-accessory-store/index.php" class="btn btn-outline-light position-absolute top-0 start-0 m-4" style="z-index:10;">&larr; Home</a>
    <div class="auth-panel">
        <div class="mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-truck" style="font-size:2.2rem;color:#3ea6ff;"></i>
            <span style="font-weight:700;font-size:1.2rem;letter-spacing:1px;">Vehicle Accessory Store</span>
        </div>
        <div class="subtitle mb-2">START FOR FREE</div>
        <h1 class="mb-1">Login <span class="accent">Account</span>.</h1>
        <div class="subtitle mb-4">Don't have an account? <a href="/vehicle-accessory-store/auth/register.php">Register</a></div>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary flex-fill">Login</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/vehicle-accessory-store/assets/js/vehicle-animation.js"></script>
</body>
</html> 