<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Get cart items
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.image_path 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Fetch user address for prefill
$user_address = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_address = $stmt->fetchColumn();
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
    }
    
    header("Location: cart.php");
    exit();
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout_confirm'])) {
    $delivery_address = trim($_POST['delivery_address']);
    $payment_method = $_POST['payment_method'];
    try {
        $conn->beginTransaction();
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, delivery_address, total_amount, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $delivery_address, $total, $payment_method]);
        $order_id = $conn->lastInsertId();
        // Add order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $conn->commit();
        $_SESSION['success'] = "Order placed successfully!";
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Checkout failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { background: #181a1b; color: #f1f1f1; }
        .cart-panel {
            background: rgba(36, 36, 36, 0.97);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            border-radius: 2rem;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1000px;
            border: 1px solid #333;
        }
        .cart-table th, .cart-table td {
            vertical-align: middle;
            background: #23272b;
            color: #f1f1f1;
        }
        .cart-table th {
            background: #181a1b;
            color: #60b8ff;
            font-weight: 600;
            border-bottom: 2px solid #333;
        }
        .cart-table img {
            width: 60px; height: 60px; object-fit: cover; border-radius: 0.7rem; box-shadow: 0 2px 8px #0002;
        }
        .quantity-group {
            display: flex; align-items: center; gap: 0.5rem;
        }
        .quantity-btn {
            background: #3ea6ff; color: #fff; border: none; border-radius: 0.5rem; width: 2rem; height: 2rem; font-size: 1.2rem; font-weight: 700; transition: background 0.2s;
        }
        .quantity-btn:hover { background: #60b8ff; }
        .empty-cart {
            text-align: center; padding: 4rem 0;
        }
        .empty-cart i {
            font-size: 4rem; color: #3ea6ff;
        }
        .empty-cart h4 { margin-top: 1.5rem; color: #e5e7eb; }
        .empty-cart p { color: #b0b8c1; }
        .alert-success, .alert-danger, .alert-info {
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
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
                <i class="bi bi-house-door-fill me-2"></i> Home
            </a>
        </div>
        <div class="cart-panel">
            <h2 class="mb-4"><i class="bi bi-cart4 accent"></i> Shopping <span class="accent">Cart</span></h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h4>Your cart is empty</h4>
                    <p>Looks like you haven't added anything yet.</p>
                    <a href="/vehicle-accessory-store/index.php" class="btn btn-primary mt-3">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['image_path'] ? $item['image_path'] : '/vehicle-accessory-store/product_images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.onerror=null;this.src='/vehicle-accessory-store/product_images/default.jpg';" class="me-3">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                    </td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" class="quantity-group">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="update" value="1" class="quantity-btn" onclick="this.form.quantity.value=Math.max(1,parseInt(this.form.quantity.value)-1);">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm text-center" style="width: 60px;">
                                            <button type="submit" name="update" value="1" class="quantity-btn" onclick="this.form.quantity.value=parseInt(this.form.quantity.value)+1;">+</button>
                                        </form>
                                    </td>
                                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="quantity" value="0">
                                            <button type="submit" name="update" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="/vehicle-accessory-store/index.php" class="btn btn-secondary">Continue Shopping</a>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal">Proceed to Checkout</button>
                </div>
                <!-- Checkout Modal -->
                <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content bg-dark text-light">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <textarea class="form-control bg-dark text-light" id="delivery_address" name="delivery_address" required rows="3"><?php echo htmlspecialchars($user_address); ?></textarea>
                          </div>
                          <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-control bg-dark text-light" id="payment_method" name="payment_method" required>
                              <option value="COD" selected>Cash on Delivery (COD)</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <strong>Total: ₱<?php echo number_format($total, 2); ?></strong>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="checkout_confirm" class="btn btn-primary">Place Order</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 