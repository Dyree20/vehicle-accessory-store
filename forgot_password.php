<?php
session_start();
require_once 'config/database.php';

$step = 1;
$email = '';
$question = '';
$user_id = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Email submitted
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("SELECT id, security_question_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && $user['security_question_id']) {
            $user_id = $user['id'];
            $question_id = $user['security_question_id'];
            $step = 2;
            // Get question text
            $stmt = $conn->prepare("SELECT question FROM security_questions WHERE id = ?");
            $stmt->execute([$question_id]);
            $question = $stmt->fetchColumn();
            $_SESSION['reset_user_id'] = $user_id;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_question'] = $question;
        } else {
            $error = 'No account found with that email or no security question set.';
        }
    }
    // Step 2: Security answer submitted
    elseif (isset($_POST['security_answer'])) {
        $user_id = $_SESSION['reset_user_id'] ?? null;
        $email = $_SESSION['reset_email'] ?? '';
        $question = $_SESSION['reset_question'] ?? '';
        $answer = trim($_POST['security_answer']);
        if ($user_id) {
            $stmt = $conn->prepare("SELECT security_answer FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $correct = $stmt->fetchColumn();
            if ($correct && strtolower(trim($correct)) === strtolower($answer)) {
                $step = 3;
            } else {
                $error = 'Incorrect answer.';
                $step = 2;
            }
        } else {
            $error = 'Session expired. Please try again.';
            $step = 1;
        }
    }
    // Step 3: New password submitted
    elseif (isset($_POST['new_password'], $_POST['confirm_password'])) {
        $user_id = $_SESSION['reset_user_id'] ?? null;
        $email = $_SESSION['reset_email'] ?? '';
        $question = $_SESSION['reset_question'] ?? '';
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        if ($user_id && $new_password === $confirm_password && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            session_unset();
            $_SESSION['success'] = 'Password reset successful. You can now log in.';
            header('Location: auth/login.php');
            exit();
        } else {
            $error = 'Passwords do not match or are too short.';
            $step = 3;
        }
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
    <style>
        body { background: #181a1b; color: #f1f1f1; }
        .reset-panel {
            background: #23272b;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            padding: 3.5rem 2.5rem;
            max-width: 540px;
            margin: 4rem auto;
        }
        .form-label { color: #60b8ff; font-weight: 500; }
        .btn-primary {
            width: 100%;
            padding: 0.75rem 0;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            background: #3ea6ff;
            color: #fff;
            border: none;
            transition: background 0.2s;
            box-shadow: none;
        }
        .btn-primary:hover {
            background: #60b8ff;
            color: #fff;
        }
        .reset-panel .form-control {
            background: #181a1b;
            color: #f1f1f1;
            border: 1px solid #333;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-danger { background: #2d1a1a; color: #ffb3b3; border: 1px solid #a94442; border-radius: 1rem; }
    </style>
</head>
<body>
    <div class="reset-panel">
        <h3 class="mb-4 text-center">Forgot Password</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($step === 1): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Enter your email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Security Question</label>
                    <div class="mb-2" style="color:#3ea6ff;font-weight:600;"><?php echo htmlspecialchars($question); ?></div>
                    <input type="text" class="form-control" name="security_answer" placeholder="Your answer" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
        <?php elseif ($step === 3): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        <?php endif; ?>
        <div class="mt-3 text-center">
            <a href="auth/login.php" class="text-decoration-none" style="color:#3ea6ff;">Back to Login</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 