<?php
session_start();
require_once 'config/database.php';

// Get categories for navigation
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get products
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT p.*, c.name as category_name, u.username 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN users u ON p.user_id = u.id 
          WHERE 1=1";

if ($category_id) {
    $query .= " AND p.category_id = ?";
}
if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
}
$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$params = [];
if ($category_id) {
    $params[] = $category_id;
}
if ($search) {
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get cart count
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <style>
        body {
            background: #181a1b;
            color: #f1f1f1;
        }
        .card {
            background: #23272b;
            border: none;
            box-shadow: 0 2px 16px rgba(0,0,0,0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 6px 24px rgba(0,0,0,0.6);
        }
        .card-title, .card-text, .text-muted {
            color: #f1f1f1 !important;
        }
        .btn-primary, .btn-success {
            border: none;
        }
        .btn-primary {
            background: #007bff;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .alert-success {
            background: #1a2d1a;
            color: #b3ffb3;
            border: 1px solid #28a745;
        }
        .navbar, .navbar-dark.bg-dark {
            background: #181a1b !important;
        }
        .hero-banner img {
            filter: brightness(0.5);
        }
        .card-img-top {
            background: #181a1b;
        }
    </style>
</head>
<body>
    <div class="hero-banner position-relative mb-5">
        <img src="/vehicle-accessory-store/assets/images/hero.jpg" alt="Vehicle Accessories" class="w-100" style="max-height:340px; object-fit:cover; filter:brightness(0.5);">
        <div class="hero-text position-absolute top-50 start-50 translate-middle text-center text-light" style="width:100%;">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Upgrade Your Ride</h1>
            <p class="lead animate__animated animate__fadeInUp">Find the best accessories for your vehicle in the Philippines</p>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/vehicle-accessory-store/index.php">Vehicle Accessory Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/vehicle-accessory-store/index.php">Home</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/index.php?category=<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form class="d-flex me-3" action="/vehicle-accessory-store/index.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/products/add.php">Add Product</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/cart.php">
                                <i class="bi bi-cart"></i> Cart
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($product['image_path'] ? $product['image_path'] : '/vehicle-accessory-store/product_images/default.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Category: <?php echo htmlspecialchars($product['category_name']); ?><br>
                                    Seller: <?php echo htmlspecialchars($product['username']); ?>
                                </small>
                            </p>
                            <p class="card-text"><strong>₱<?php echo number_format($product['price'], 2); ?></strong></p>
                            <div class="d-grid gap-2">
                                <a href="/vehicle-accessory-store/products/view.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="/vehicle-accessory-store/cart/add.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 