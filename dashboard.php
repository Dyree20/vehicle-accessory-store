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
           GROUP_CONCAT(p.name SEPARATOR ', ') as products,
           GROUP_CONCAT(p.user_id SEPARATOR ', ') as product_seller_ids,
           GROUP_CONCAT(oi.product_id SEPARATOR ', ') as product_ids
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
            WHERE oi.product_id = p.id AND o.status = 'delivered') as total_sales
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

// Handle seller set delivery date
if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller' &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['set_delivery_date_order_id'], $_POST['delivery_date'])) {
    $order_id = intval($_POST['set_delivery_date_order_id']);
    $delivery_date = $_POST['delivery_date'];
    
    // First verify the order belongs to this seller and check current status
    $stmt = $conn->prepare("
        SELECT o.* FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND p.user_id = ? AND o.status IN ('pending', 'processing')
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if ($order) {
        // Update the order status and delivery date
        $stmt = $conn->prepare("
            UPDATE orders 
            SET delivery_date = ?, 
                status = 'order placed',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? 
            AND status IN ('pending', 'processing')
        ");
        
        if ($stmt->execute([$delivery_date, $order_id])) {
            // Verify the update was successful
            $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $updated_order = $stmt->fetch();
            
            if ($updated_order && $updated_order['status'] === 'order placed') {
                $_SESSION['success'] = 'Delivery date set. Order is now placed.';
            } else {
                $_SESSION['error'] = 'Failed to update order status.';
            }
        } else {
            $_SESSION['error'] = 'Failed to update order.';
        }
    } else {
        $_SESSION['error'] = 'Invalid order or order cannot be updated.';
    }
    header('Location: dashboard.php');
    exit();
}

// Handle seller set courier
if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller' &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['set_courier_order_id'], $_POST['courier'])) {
    $order_id = intval($_POST['set_courier_order_id']);
    $courier = $_POST['courier'];
    $stmt = $conn->prepare("UPDATE orders SET courier = ?, status = 'on delivery', total_amount = total_amount + 60 WHERE id = ? AND status = 'order placed' AND delivery_date IS NOT NULL");
    $stmt->execute([$courier, $order_id]);
    $_SESSION['success'] = 'Courier set. Order is now on delivery.';
    header('Location: dashboard.php');
    exit();
}

// Handle seller mark as delivered
if (
    isset($_SESSION['role']) && $_SESSION['role'] === 'seller' &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['deliver_order_id'])
) {
    $order_id = intval($_POST['deliver_order_id']);
    // Verify the order belongs to this seller and is on delivery
    $stmt = $conn->prepare("
        SELECT o.* FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND p.user_id = ? AND o.status = 'on delivery'
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if ($order) {
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ? AND status = 'on delivery'");
        $stmt->execute([$order_id]);
        $_SESSION['success'] = 'Order marked as completed.';
    } else {
        $_SESSION['error'] = 'Invalid order or order cannot be updated.';
    }
    header('Location: dashboard.php');
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
    <div class="container-fluid mt-4" style="max-width: 1400px;">
        <div class="d-flex justify-content-end mb-3">
            <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
                <i class="bi bi-house-door-fill me-2"></i> Home
            </a>
        </div>
        <h2>Dashboard</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="row mt-4">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller'): ?>
                <h4 class="mb-4">Seller Orders</h4>
                <?php
                // Get all orders for products owned by this seller
                $stmt = $conn->prepare("
                    SELECT o.*, u.username AS buyer, oi.product_id, p.name AS product_name, oi.quantity, oi.price AS item_price
                    FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    JOIN products p ON oi.product_id = p.id
                    JOIN users u ON o.user_id = u.id
                    WHERE p.user_id = ?
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $seller_orders = $stmt->fetchAll();
                ?>
                <?php if (empty($seller_orders)): ?>
                    <p class="text-muted">No seller orders found.</p>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach (array_slice($seller_orders, 0, 5) as $order): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0" style="background:#23272b; color:#f1f1f1;">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-2">Order #<?php echo $order['id']; ?></h5>
                                        <div class="mb-2"><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?></div>
                                        <div class="mb-2"><strong>Buyer:</strong> <?php echo htmlspecialchars($order['buyer']); ?></div>
                                        <div class="mb-2"><strong>Quantity:</strong> <?php echo $order['quantity']; ?></div>
                                        <div class="mb-2"><strong>Item Price:</strong> ₱<?php echo number_format($order['item_price'], 2); ?></div>
                                        <div class="mb-2"><strong>Address:</strong> <span style="white-space:pre-line;"><?php echo htmlspecialchars($order['delivery_address']); ?></span></div>
                                        <div class="mb-2"><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
                                        <div class="mb-2"><strong>Status:</strong> <span class="badge bg-<?php 
                                            echo $order['status'] == 'completed' || $order['status'] == 'delivered' ? 'success' : 
                                                ($order['status'] == 'on delivery' ? 'info' :
                                                ($order['status'] == 'processing' ? 'warning' : 
                                                ($order['status'] == 'cancelled' ? 'danger' : 'secondary'))); 
                                        ?>">
                                            <?php echo $order['status'] == 'on delivery' ? 'On Delivery' : ucfirst($order['status']); ?>
                                        </span></div>
                                        <div class="mb-2"><strong>Delivery Date:</strong> <?php echo $order['delivery_date'] ? date('Y-m-d H:i', strtotime($order['delivery_date'])) : '<span class=\'text-muted\'>-</span>'; ?></div>
                                        <div class="mb-2"><strong>Courier:</strong> <?php echo $order['courier'] ? htmlspecialchars($order['courier']) : '<span class=\'text-muted\'>-</span>'; ?></div>
                                        <div class="mb-2"><strong>Placed At:</strong> <?php echo $order['created_at']; ?></div>
                                        <div class="mt-auto">
                                            <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                                <form method="POST" class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 mb-2">
                                                    <input type="hidden" name="set_delivery_date_order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="datetime-local" name="delivery_date" value="<?php echo $order['delivery_date'] ? date('Y-m-d\\TH:i', strtotime($order['delivery_date'])) : ''; ?>" required class="form-control form-control-sm" style="max-width:170px;">
                                                    <button type="submit" class="btn btn-info btn-sm">Set Delivery Date</button>
                                                </form>
                                            <?php elseif ($order['status'] === 'order placed' && !$order['courier']): ?>
                                                <form method="POST" class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 mb-2">
                                                    <input type="hidden" name="set_delivery_date_order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="datetime-local" name="delivery_date" value="<?php echo $order['delivery_date'] ? date('Y-m-d\\TH:i', strtotime($order['delivery_date'])) : ''; ?>" required class="form-control form-control-sm" style="max-width:170px;">
                                                    <button type="submit" class="btn btn-warning btn-sm">Edit Delivery Date</button>
                                                </form>
                                                <form method="POST" class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                    <input type="hidden" name="set_courier_order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="courier" class="form-control form-control-sm" required style="max-width:170px;">
                                                        <option value="">Select Courier</option>
                                                        <option value="J&T EXPRESS">J&T EXPRESS</option>
                                                        <option value="FLASH EXPRESS">FLASH EXPRESS</option>
                                                        <option value="JRS EXPRESS">JRS EXPRESS</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-info btn-sm">Set Courier</button>
                                                </form>
                                            <?php elseif ($order['status'] === 'on delivery'): ?>
                                                <form method="POST" style="display:inline;max-width:220px;">
                                                    <input type="hidden" name="deliver_order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">Mark as Delivered</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Customer dashboard (Recent Orders, Your Products, etc.) -->
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
                                                <?php
                                                // Check if the logged-in seller owns any product in this order
                                                $stmt = $conn->prepare("
                                                    SELECT COUNT(*) FROM order_items oi
                                                    JOIN products p ON oi.product_id = p.id
                                                    WHERE oi.order_id = ? AND p.user_id = ?
                                                ");
                                                $stmt->execute([$order['id'], $_SESSION['user_id']]);
                                                $is_seller_order = $stmt->fetchColumn() > 0;
                                                
                                                // Debug output
                                                echo '<!-- Debug Info:
                                                    Order ID: ' . $order['id'] . '
                                                    Seller Owns: ' . ($is_seller_order ? 'YES' : 'NO') . '
                                                    Session User ID: ' . $_SESSION['user_id'] . '
                                                    Order Status: ' . $order['status'] . '
                                                    Delivery Date: ' . ($order['delivery_date'] ?? 'NULL') . '
                                                -->';
                                                ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                    <td><?php echo $order['item_count']; ?> items</td>
                                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $order['status'] == 'completed' ? 'success' : 
                                                                ($order['status'] == 'on delivery' ? 'info' :
                                                                ($order['status'] == 'processing' ? 'warning' : 
                                                                ($order['status'] == 'cancelled' ? 'danger' : 'secondary'))); 
                                                        ?>">
                                                            <?php echo $order['status'] == 'on delivery' ? 'On Delivery' : ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller' && $is_seller_order):
                                                            // Debug output for form visibility
                                                            echo '<!-- Form Visibility Debug:
                                                                Role: ' . $_SESSION['role'] . '
                                                                Is Seller Order: ' . ($is_seller_order ? 'YES' : 'NO') . '
                                                                Order Status: ' . $order['status'] . '
                                                                Delivery Date: ' . ($order['delivery_date'] ?? 'NULL') . '
                                                            -->';
                                                            
                                                            if ($order['status'] === 'pending'): ?>
                                                                <form method="POST" class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                                    <input type="hidden" name="set_delivery_date_order_id" value="<?php echo $order['id']; ?>">
                                                                    <input type="datetime-local" name="delivery_date" required class="form-control form-control-sm" style="max-width:170px;">
                                                                    <button type="submit" class="btn btn-info btn-sm">Set Delivery Date</button>
                                                                </form>
                                                            <?php elseif ($order['status'] === 'order placed' && $order['delivery_date'] && !$order['courier']): ?>
                                                                <form method="POST" class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                                    <input type="hidden" name="set_courier_order_id" value="<?php echo $order['id']; ?>">
                                                                    <select name="courier" class="form-control form-control-sm" required style="max-width:170px;">
                                                                        <option value="">Select Courier</option>
                                                                        <option value="J&T EXPRESS">J&T EXPRESS</option>
                                                                        <option value="FLASH EXPRESS">FLASH EXPRESS</option>
                                                                        <option value="JRS EXPRESS">JRS EXPRESS</option>
                                                                    </select>
                                                                    <button type="submit" class="btn btn-info btn-sm">Set Courier</button>
                                                                </form>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif;
                                                        else: ?>
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
                                                <th>Action</th>
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
                                                    <td>
                                                        <a href="/vehicle-accessory-store/products/edit.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm" style="border-radius:0.7rem;font-weight:600;">Edit</a>
                                                    </td>
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
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 