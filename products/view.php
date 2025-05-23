<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Get product details
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, u.username 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: ../index.php");
    exit();
}

// Helper for image path
function get_product_image_url($product) {
    $img = $product['image_path'] ?? '';
    if (!$img) return '/vehicle-accessory-store/product_images/default.jpg';
    if (strpos($img, '/vehicle-accessory-store/') === 0) return $img;
    return '/vehicle-accessory-store/' . ltrim($img, '/');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { background: #181a1b; color: #f1f1f1; }
        .product-card {
            background: rgba(36, 36, 36, 0.97);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            border-radius: 2rem;
            padding: 2.5rem 2rem;
            max-width: 1200px;
            margin: 3rem auto;
            display: flex;
            gap: 2.5rem;
            align-items: flex-start;
        }
        .product-image {
            width: 340px;
            height: 340px;
            object-fit: cover;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            background: #23272b;
        }
        .product-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .product-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #e5e7eb;
        }
        .product-meta {
            color: #60b8ff;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .product-price {
            color: #3ea6ff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .product-desc {
            font-size: 1.15rem;
            margin-bottom: 1.5rem;
            color: #e5e7eb;
            white-space: pre-line;
        }
        .btn-primary {
            background: #3ea6ff;
            color: #fff;
            border: none;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            padding: 0.8rem 2.2rem;
            margin-top: 0.5rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 12px #3ea6ff44;
        }
        .btn-primary:hover {
            background: #60b8ff;
            color: #fff;
        }
        .home-btn {
            position: fixed;
            top: 2rem;
            right: 3rem;
            z-index: 10;
        }
        @media (max-width: 900px) {
            .product-card {
                flex-direction: column;
                align-items: center;
                padding: 1.5rem 0.5rem;
                gap: 1.5rem;
            }
            .product-image {
                width: 100%;
                max-width: 340px;
                height: 220px;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>
    <div class="product-card">
        <img src="<?php echo htmlspecialchars(get_product_image_url($product)); ?>"
             class="product-image"
             alt="<?php echo htmlspecialchars($product['name']); ?>"
             onerror="this.onerror=null;this.src='/vehicle-accessory-store/product_images/default.jpg';">
        <div class="product-details">
            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
            <div class="product-meta">
                Category: <?php echo htmlspecialchars($product['category_name']); ?><br>
                Seller: <?php echo htmlspecialchars($product['username']); ?>
            </div>
            <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
            <div class="mb-2">
                <?php if (isset($product['stock'])): ?>
                    <?php if ($product['stock'] == 0): ?>
                        <span class="badge bg-danger">SOLD OUT</span>
                    <?php else: ?>
                        <span class="badge bg-success">In Stock: <?php echo (int)$product['stock']; ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="product-desc"><?php echo htmlspecialchars($product['description']); ?></div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="/vehicle-accessory-store/cart/add.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-primary" <?php echo (isset($product['stock']) && $product['stock'] == 0) ? 'disabled' : ''; ?>>Add to Cart</button>
                </form>
                <?php if ($_SESSION['user_id'] == $product['user_id']): ?>
                    <a href="/vehicle-accessory-store/products/edit.php?id=<?php echo $product['id']; ?>" class="btn btn-warning mt-2" style="border-radius:1rem;font-weight:600;">Edit Product</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 