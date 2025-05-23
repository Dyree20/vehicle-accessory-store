<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

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

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo '<div class="alert alert-danger">User not found.</div>';
    exit();
}

$question_text = isset($security_questions[$user['security_question_id']]) ? $security_questions[$user['security_question_id']] : 'N/A';

// Fetch all phone numbers for the user
$stmt = $conn->prepare("SELECT * FROM user_phones WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_phones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { background: #181a1b; color: #f1f1f1; }
        .profile-card {
            background: #23272b;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            padding: 2.5rem 2rem;
            max-width: 500px;
            margin: 3rem auto;
        }
        .profile-icon-lg {
            font-size: 4rem;
            color: #3ea6ff;
        }
        .profile-label {
            color: #60b8ff;
            font-weight: 500;
        }
        .profile-value {
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-end mb-3" style="max-width:1400px;margin:auto;">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>
    <div class="container-fluid" style="max-width:1800px;min-height:90vh;">
        <div class="row justify-content-center align-items-start g-4 flex-nowrap" style="margin-top:40px;">
            <!-- Left Card: Avatar and Basic Info -->
            <div class="col-12 col-md-4 col-lg-3">
                <div class="p-4 text-center h-100 d-flex flex-column justify-content-center" style="background:#23272b;border-radius:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);min-height:350px;">
                    <i class="bi bi-person-circle profile-icon-lg mb-3"></i>
                    <h3 class="mb-1" style="color:#fff;word-break:break-all;"><?php echo htmlspecialchars($user['username']); ?></h3>
                    <div class="mb-2 text-capitalize" style="color:#60b8ff;font-weight:600;">Role: <?php echo htmlspecialchars($user['role']); ?></div>
                    <div class="mb-2" style="color:#aaa;font-size:1rem;">
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </div>
                    <div class="mb-2" style="color:#aaa;font-size:0.95rem;">
                        <?php echo htmlspecialchars($user['address']); ?>
                    </div>
                </div>
            </div>
            <!-- Right Card: Details -->
            <div class="col-12 col-md-8 col-lg-9">
                <div class="p-4 h-100" style="background:#23272b;border-radius:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);min-height:350px;">
                    <h4 class="mb-4" style="color:#3ea6ff;">Profile Details</h4>
                    <div class="row mb-3">
                        <div class="col-4 profile-label">Full Name:</div>
                        <div class="col-8 profile-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 profile-label">Email:</div>
                        <div class="col-8 profile-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 profile-label">Phone:</div>
                        <div class="col-8 profile-value">
                            <ul class="list-unstyled mb-0" id="phone-list">
                                <?php if (count($user_phones) > 0): ?>
                                    <?php foreach ($user_phones as $phone): ?>
                                        <li class="mb-1 d-flex align-items-center">
                                            <span class="phone-text"><?php echo htmlspecialchars($phone['phone']); ?></span>
                                            <button class="btn btn-sm btn-outline-info ms-2 edit-phone-btn" data-id="<?php echo $phone['id']; ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <form method="POST" action="profile.php" class="d-inline ms-1">
                                                <input type="hidden" name="delete_phone_id" value="<?php echo $phone['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this phone number?')"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="mb-1 d-flex align-items-center">
                                        <span class="phone-text"><?php echo htmlspecialchars($user['phone']); ?></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <button class="btn btn-sm btn-outline-success mt-2" id="add-phone-btn" title="Add Phone"><i class="bi bi-plus"></i></button>
                            <form method="POST" action="profile.php" id="add-phone-form" class="d-none mt-2">
                                <input type="text" name="new_phone" class="form-control form-control-sm d-inline-block" style="width:180px;" placeholder="Enter phone number" required>
                                <button type="submit" class="btn btn-sm btn-success ms-1">Save</button>
                                <button type="button" class="btn btn-sm btn-secondary ms-1" id="cancel-add-phone">Cancel</button>
                            </form>
                            <form method="POST" action="profile.php" id="edit-phone-form" class="d-none mt-2">
                                <input type="hidden" name="edit_phone_id" id="edit-phone-id">
                                <input type="text" name="edit_phone" id="edit-phone-input" class="form-control form-control-sm d-inline-block" style="width:180px;" required>
                                <button type="submit" class="btn btn-sm btn-info ms-1">Update</button>
                                <button type="button" class="btn btn-sm btn-secondary ms-1" id="cancel-edit-phone">Cancel</button>
                            </form>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 profile-label">Address:</div>
                        <div class="col-8 profile-value"><?php echo htmlspecialchars($user['address']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 profile-label">Role:</div>
                        <div class="col-8 profile-value text-capitalize"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 profile-label">Username:</div>
                        <div class="col-8 profile-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('add-phone-btn').onclick = function() {
        document.getElementById('add-phone-form').classList.remove('d-none');
        this.classList.add('d-none');
    };
    document.getElementById('cancel-add-phone').onclick = function() {
        document.getElementById('add-phone-form').classList.add('d-none');
        document.getElementById('add-phone-btn').classList.remove('d-none');
    };
    document.querySelectorAll('.edit-phone-btn').forEach(function(btn) {
        btn.onclick = function() {
            var phoneId = this.getAttribute('data-id');
            var phoneText = this.parentElement.querySelector('.phone-text').textContent;
            document.getElementById('edit-phone-id').value = phoneId;
            document.getElementById('edit-phone-input').value = phoneText;
            document.getElementById('edit-phone-form').classList.remove('d-none');
            document.getElementById('add-phone-btn').classList.add('d-none');
        };
    });
    document.getElementById('cancel-edit-phone').onclick = function() {
        document.getElementById('edit-phone-form').classList.add('d-none');
        document.getElementById('add-phone-btn').classList.remove('d-none');
    };
    </script>
</body>
</html>

// Handle add, edit, delete phone POST actions at the top of the file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_phone'])) {
        $stmt = $conn->prepare("INSERT INTO user_phones (user_id, phone) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_POST['new_phone']]);
        header('Location: profile.php'); exit();
    }
    if (isset($_POST['edit_phone_id'], $_POST['edit_phone'])) {
        $stmt = $conn->prepare("UPDATE user_phones SET phone = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['edit_phone'], $_POST['edit_phone_id'], $_SESSION['user_id']]);
        header('Location: profile.php'); exit();
    }
    if (isset($_POST['delete_phone_id'])) {
        $stmt = $conn->prepare("DELETE FROM user_phones WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['delete_phone_id'], $_SESSION['user_id']]);
        header('Location: profile.php'); exit();
    }
} 