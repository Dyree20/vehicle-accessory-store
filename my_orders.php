<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           GROUP_CONCAT(p.name SEPARATOR ', ') as products
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = intval($_POST['cancel_order_id']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending', 'processing')");
    $stmt->execute([$cancel_order_id, $_SESSION['user_id']]);
    $_SESSION['success'] = "Order cancelled successfully.";
    header("Location: my_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { 
            background: #181a1b; 
            color: #f1f1f1; 
            min-height: 100vh;
        }
        .orders-panel {
            background: rgba(36, 36, 36, 0.97);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            border-radius: 2rem;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
            border: 1px solid #333;
        }
        .orders-panel h2 {
            color: #e5e7eb;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .orders-panel h2 .accent {
            color: #3ea6ff;
        }
        .table {
            background: #23272b;
            color: #f1f1f1;
            border-radius: 1rem;
            overflow: hidden;
        }
        .table th {
            background: #181a1b;
            color: #60b8ff;
            font-weight: 600;
            border-bottom: 2px solid #333;
            padding: 1rem;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #333;
        }
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 0.7rem;
            font-weight: 600;
        }
        .btn-cancel {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 0.7rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            background: #bb2d3b;
            color: #fff;
        }
        .alert-success {
            background: #1a2d1a;
            color: #b3ffb3;
            border: 1px solid #28a745;
            border-radius: 1rem;
        }
        .home-btn {
            position: fixed;
            top: 2rem;
            right: 3rem;
            z-index: 10;
            background: #3ea6ff;
            color: #fff;
            border: none;
            border-radius: 1rem;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .home-btn:hover {
            background: #60b8ff;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>

    <div class="container">
        <div class="orders-panel">
            <h2><i class="bi bi-box-seam accent"></i> My <span class="accent">Orders</span></h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam" style="font-size: 3rem; color: #3ea6ff;"></i>
                    <h4 class="mt-3">No Orders Yet</h4>
                    <p class="text-muted">Start shopping to see your orders here!</p>
                    <a href="/vehicle-accessory-store/index.php" class="btn btn-primary mt-3">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Products</th>
                                <th>Total Items</th>
                                <th>Delivery Address</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['products']); ?></td>
                                    <td><?php echo $order['item_count']; ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['status'] == 'completed' ? 'success' : 
                                                ($order['status'] == 'processing' ? 'warning' : 
                                                ($order['status'] == 'on delivery' ? 'info' :
                                                ($order['status'] == 'cancelled' ? 'danger' : 'secondary'))); 
                                        ?>">
                                            <?php echo $order['status'] == 'on delivery' ? 'On Delivery' : ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <?php if (in_array($order['status'], ['pending', 'order placed']) || is_null($order['status'])): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="cancel_order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" class="btn btn-cancel" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                    Cancel Order
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 