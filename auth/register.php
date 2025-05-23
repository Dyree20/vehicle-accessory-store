<?php
session_start();
require_once '../config/database.php';

// Hardcoded security questions
$security_questions = [
    1 => 'What was your first pet\'s name?',
    2 => 'In which city were you born?',
    3 => 'What is your mother\'s maiden name?',
    4 => 'What was your childhood nickname?',
    5 => 'What is the name of your favorite childhood teacher?',
    6 => 'What was the make of your first car?',
    7 => 'What is your favorite movie?',
    8 => 'What is the name of the street you grew up on?',
    9 => 'What was the name of your first school?',
    10 => 'What is your favorite book?'
];

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
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, address, phone, role, security_question_id, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $full_name, $address, $phone, $role, $question_id, $answer]);
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
    <style>
        .auth-panel {
            max-width: 700px !important;
            padding: 3.5rem 2.5rem 3rem 2.5rem !important;
        }
    </style>
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
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>
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
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="col-md-6">
                    <textarea class="form-control" id="address" name="address" placeholder="Address" required></textarea>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" required>
                    <select class="form-control" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="seller">Seller</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <select class="form-control" name="question_id" required>
                        <option value="">Select a security question</option>
                        <?php foreach ($security_questions as $id => $question): ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($question); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="answer" placeholder="Your Answer" required>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary flex-fill">Create account</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/vehicle-accessory-store/assets/js/vehicle-animation.js"></script>
</body>
</html> 