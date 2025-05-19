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

// Get user's products
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name,
           (SELECT COUNT(*) FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE oi.product_id = p.id) as total_sales
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = intval($_POST['cancel_order_id']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending', 'processing')");
    $stmt->execute([$cancel_order_id, $_SESSION['user_id']]);
    $_SESSION['success'] = "Order cancelled.";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <style>
    body { background: #181a1b; color: #f1f1f1; }
    .card, .table { background: #23272b; color: #f1f1f1; }
    .card-header { background: #181a1b; color: #fff; border-bottom: 1px solid #333; }
    .table th, .table td { vertical-align: middle; }
    .btn-primary { background: #007bff; border: none; }
    .btn-primary:hover { background: #0056b3; }
    .btn-danger { background: #dc3545; border: none; }
    .btn-danger:hover { background: #a71d2a; }
    .btn-secondary { background: #343a40; border: none; color: #fff; }
    .btn-secondary:hover { background: #23272b; }
    .alert-success { background: #1a2d1a; color: #b3ffb3; border: 1px solid #28a745; }

    /* Fix modal z-index and overflow issues */
    .modal { z-index: 2000; }
    .modal-backdrop { z-index: 1999; }
    .container, .card { overflow: visible !important; }
    .modal-content { background: #23272b; color: #f1f1f1; border: 1px solid #444; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="/vehicle-accessory-store/index.php" class="btn btn-secondary btn-lg px-4 py-2 fw-bold" style="font-size:1.25rem;">Home</a>
        </div>
        <h2>Dashboard</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Recent Orders</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <p class="text-muted">No orders found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td><?php echo $order['item_count']; ?> items</td>
                                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $order['status'] == 'completed' ? 'success' : 
                                                            ($order['status'] == 'processing' ? 'warning' : 
                                                            ($order['status'] == 'cancelled' ? 'danger' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                                        <!-- Cancel Order Button trigger modal -->
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $order['id']; ?>">Cancel</button>
                                                        <!-- Modal -->
                                                        <div class="modal fade" id="cancelModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                                          <div class="modal-dialog">
                                                            <div class="modal-content bg-dark text-light">
                                                              <form method="POST">
                                                                <div class="modal-header">
                                                                  <h5 class="modal-title" id="cancelModalLabel<?php echo $order['id']; ?>">Cancel Order</h5>
                                                                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                  Are you sure you want to cancel order #<?php echo $order['id']; ?>?
                                                                </div>
                                                                <div class="modal-footer">
                                                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                                  <button type="submit" name="cancel_order_id" value="<?php echo $order['id']; ?>" class="btn btn-danger">Yes, Cancel</button>
                                                                </div>
                                                              </form>
                                                            </div>
                                                          </div>
                                                        </div>
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
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Your Products</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <p class="text-muted">No products found.</p>
                            <a href="/vehicle-accessory-store/products/add.php" class="btn btn-primary">Add Product</a>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                             style="width: 40px; height: 40px; object-fit: cover;" 
                                                             class="me-2">
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo $product['total_sales']; ?> sold</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="/vehicle-accessory-store/products/add.php" class="btn btn-primary">Add New Product</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 