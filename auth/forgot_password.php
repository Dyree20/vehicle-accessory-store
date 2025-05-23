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

$step = 1;
$question = '';
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username_or_email'])) {
        $input = trim($_POST['username_or_email']);
        $stmt = $conn->prepare("SELECT id, security_question_id, security_answer FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$input, $input]);
        $row = $stmt->fetch();
        if ($row) {
            $step = 2;
            $question = $security_questions[$row['security_question_id']];
            $user_id = $row['id'];
            $_SESSION['reset_user_id'] = $user_id;
            $_SESSION['security_answer'] = $row['security_answer'];
        } else {
            $error = 'User not found.';
        }
    } elseif (isset($_POST['security_answer'])) {
        $user_id = $_SESSION['reset_user_id'] ?? null;
        $answer = trim($_POST['security_answer']);
        $stored_answer = $_SESSION['security_answer'] ?? '';
        
        if ($user_id && strtolower($answer) === strtolower($stored_answer)) {
            $step = 3;
        } else {
            $error = 'Incorrect answer.';
            $step = 2;
            // Get the question again
            $stmt = $conn->prepare("SELECT security_question_id FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $question_id = $stmt->fetchColumn();
            $question = $security_questions[$question_id];
        }
    } elseif (isset($_POST['new_password'])) {
        $user_id = $_SESSION['reset_user_id'] ?? null;
        if (!$user_id) {
            header('Location: login.php');
            exit();
        }
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['security_answer']);
        $_SESSION['success'] = 'Password reset successful! You can now log in.';
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
</head>
<body class="auth-bg">
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>
    <div class="auth-panel">
        <h2 class="mb-3">Forgot Password</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($step === 1): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username_or_email" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username_or_email" name="username_or_email" required>
                </div>
                <button type="submit" class="btn btn-primary">Next</button>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Security Question</label>
                    <div class="mb-2 fw-bold"><?php echo htmlspecialchars($question); ?></div>
                    <input type="text" class="form-control" name="security_answer" placeholder="Your Answer" required>
                </div>
                <button type="submit" class="btn btn-primary">Next</button>
            </form>
        <?php elseif ($step === 3): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
        <div class="mt-3">
            <a href="login.php" class="text-light">Back to Login</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 