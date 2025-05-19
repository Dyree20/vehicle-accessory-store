<?php
session_start();
require_once '../config/database.php';

$step = 1;
$question = '';
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username_or_email'])) {
        $input = trim($_POST['username_or_email']);
        $stmt = $conn->prepare("SELECT u.id, q.question FROM users u JOIN user_security_answers usa ON u.id = usa.user_id JOIN security_questions q ON usa.question_id = q.id WHERE u.username = ? OR u.email = ?");
        $stmt->execute([$input, $input]);
        $row = $stmt->fetch();
        if ($row) {
            $step = 2;
            $question = $row['question'];
            $user_id = $row['id'];
            $_SESSION['reset_user_id'] = $user_id;
        } else {
            $error = 'User not found or no security question set.';
        }
    } elseif (isset($_POST['security_answer'])) {
        $user_id = $_SESSION['reset_user_id'] ?? null;
        $answer = trim($_POST['security_answer']);
        $stmt = $conn->prepare("SELECT answer FROM user_security_answers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        if ($row && strtolower($row['answer']) === strtolower($answer)) {
            $step = 3;
        } else {
            $error = 'Incorrect answer.';
            $step = 2;
            // Get the question again
            $stmt = $conn->prepare("SELECT q.question FROM user_security_answers usa JOIN security_questions q ON usa.question_id = q.id WHERE usa.user_id = ?");
            $stmt->execute([$user_id]);
            $question = $stmt->fetchColumn();
        }
    } elseif (isset($_POST['new_password'])) {
        $user_id = $_SESSION['reset_user_id'] ?? null;
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);
        unset($_SESSION['reset_user_id']);
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