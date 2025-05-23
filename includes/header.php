<?php
// Get categories for navigation if not already set
if (!isset($categories)) {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
}
// Get cart count if not already set
if (!isset($cart_count)) {
    $cart_count = 0;
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_count = $stmt->fetchColumn();
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Logo/Icon -->
        <a class="navbar-brand d-flex align-items-center" href="/vehicle-accessory-store/index.php">
            <i class="bi bi-shop me-2" style="font-size: 2rem;"></i>
            Vehicle Accessory Store
        </a>
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
                <input class="form-control me-2" type="search" name="search" placeholder="Search products" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/vehicle-accessory-store/cart.php">
                            <i class="bi bi-cart"></i> Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'seller'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/products/add.php">Add Product</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/my_products.php" id="my-products-link">My Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/dashboard.php">Seller Dashboard</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'customer'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/my_orders.php">My Orders</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vehicle-accessory-store/dashboard.php">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
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
            <div class="d-flex align-items-center ms-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Profile Icon -->
                    <a href="/vehicle-accessory-store/profile.php" class="profile-icon ms-3" title="Profile">
                        <i class="bi bi-person-circle" style="font-size: 2rem; color: #3ea6ff;"></i>
                    </a>
                <?php else: ?>
                    <a href="/vehicle-accessory-store/auth/login.php" class="btn btn-primary ms-3">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<style>
.profile-icon {
    transition: color 0.2s;
}
.profile-icon:hover {
    color: #60b8ff !important;
    text-decoration: none;
}
</style> 