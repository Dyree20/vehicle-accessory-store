<?php
session_start();
require_once '../config/database.php';

// Fetch security questions
$questions = $conn->query("SELECT * FROM security_questions")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $question_id = intval($_POST['question_id']);
    $answer = trim($_POST['answer']);

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, address, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $full_name, $address, $phone, $role]);
        $user_id = $conn->lastInsertId();
        $stmt = $conn->prepare("INSERT INTO user_security_answers (user_id, question_id, answer) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $question_id, $answer]);
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vehicle Accessory Store</title>
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
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com.svg" class="vehicle9" alt="Animated Car 9" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (1).svg" class="vehicle10" alt="Animated Car 10" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (2).svg" class="vehicle11" alt="Animated Car 11" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (3).svg" class="vehicle12" alt="Animated Car 12" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com.svg" class="vehicle13" alt="Animated Car 13" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (1).svg" class="vehicle14" alt="Animated Car 14" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (2).svg" class="vehicle15" alt="Animated Car 15" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (3).svg" class="vehicle16" alt="Animated Car 16" />
    </div>
    <a href="/vehicle-accessory-store/index.php" class="btn btn-outline-light position-absolute top-0 start-0 m-4" style="z-index:10;">&larr; Home</a>
    <div class="auth-panel">
        <div class="mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-gear" style="font-size:2.2rem;color:#3ea6ff;"></i>
            <span style="font-weight:700;font-size:1.2rem;letter-spacing:1px;">Vehicle Accessory Store</span>
        </div>
        <div class="subtitle mb-2">START FOR FREE</div>
        <h1 class="mb-1">Create <span class="accent">new account</span>.</h1>
        <div class="subtitle mb-4">Already a member? <a href="/vehicle-accessory-store/auth/login.php">Log in</a></div>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
            <textarea class="form-control" id="address" name="address" placeholder="Address" required></textarea>
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" required>
            <select class="form-control mt-2" name="role" required>
                <option value="customer">Customer</option>
                <option value="seller">Seller</option>
            </select>
            <select class="form-control mt-2" name="question_id" required>
                <option value="">Select a security question</option>
                <?php foreach ($questions as $q): ?>
                    <option value="<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['question']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" class="form-control mt-2" name="answer" placeholder="Your Answer" required>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary flex-fill">Create account</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/vehicle-accessory-store/assets/js/vehicle-animation.js"></script>
</body>
</html> 