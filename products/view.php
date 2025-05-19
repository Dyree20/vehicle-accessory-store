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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
</head>
<body>
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/vehicle-accessory-store/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="/vehicle-accessory-store/index.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                     class="img-fluid rounded" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-muted">
                    Category: <?php echo htmlspecialchars($product['category_name']); ?><br>
                    Seller: <?php echo htmlspecialchars($product['username']); ?>
                </p>
                <h3 class="text-primary">₱<?php echo number_format($product['price'], 2); ?></h3>
                <p class="mt-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="/vehicle-accessory-store/cart/add.php" method="POST" class="mt-4">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mt-4">
                        Please <a href="/vehicle-accessory-store/auth/login.php">login</a> to add this item to your cart.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 