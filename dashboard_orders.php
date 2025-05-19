<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    $_SESSION['success'] = "Order status updated.";
    header("Location: dashboard_orders.php");
    exit();
}

// Get all orders for products owned by this seller
$stmt = $conn->prepare("
    SELECT o.*, u.username AS buyer, oi.product_id, p.name AS product_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Orders Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <style>
        body { background: #181a1b; color: #f1f1f1; }
        .table { background: #23272b; color: #f1f1f1; }
        .table th, .table td { vertical-align: middle; }
        .table thead { background: #181a1b; }
        .btn, .form-select { border: none; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #a71d2a; }
        .alert-success { background: #1a2d1a; color: #b3ffb3; border: 1px solid #28a745; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn btn-secondary btn-lg px-4 py-2 fw-bold" style="font-size:1.25rem;">Home</a>
    </div>
    <h2 class="mb-4">Seller Orders Dashboard</h2>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Address</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Placed At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['buyer']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></td>
                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                    <td>
                        <form method="POST" class="d-flex align-items-center">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" class="form-select bg-dark text-light me-2">
                                <option value="pending" <?php if($order['status']==='pending') echo 'selected'; ?>>Pending</option>
                                <option value="processing" <?php if($order['status']==='processing') echo 'selected'; ?>>Processing</option>
                                <option value="completed" <?php if($order['status']==='completed') echo 'selected'; ?>>Completed</option>
                                <option value="cancelled" <?php if($order['status']==='cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                    <td><?php echo $order['created_at']; ?></td>
                    <td>
                        <?php if($order['status']!=='cancelled'): ?>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                            </form>
                        <?php else: ?>
                            <span class="text-danger">Cancelled</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 