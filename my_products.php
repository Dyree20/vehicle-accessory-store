<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: index.php');
    exit();
}

// Fetch seller's products
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.user_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();

function safe_stock($product) {
    return isset($product['stock']) ? htmlspecialchars($product['stock']) : '<span class="text-muted">N/A</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <style>
        body { background: #181a1b; color: #f1f1f1; }
        .card, .table { background: #23272b; color: #f1f1f1; }
        .card-header { background: #181a1b; color: #fff; border-bottom: 1px solid #333; }
        .table th, .table td { vertical-align: middle; }
        .btn-primary { background: #007bff; border: none; }
        .btn-primary:hover { background: #0056b3; }
        .btn-warning { background: #ffc107; color: #23272b; border: none; }
        .btn-warning:hover { background: #e0a800; color: #fff; }
        .btn-danger { background: #dc3545; border: none; }
        .btn-danger:hover { background: #a71d2a; }
        .btn-secondary { background: #343a40; border: none; color: #fff; }
        .btn-secondary:hover { background: #23272b; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container-fluid mt-4" style="max-width: 1200px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>My Products</h2>
        <a href="/vehicle-accessory-store/products/add.php" class="btn btn-primary">Add New Product</a>
    </div>
    <?php if (empty($products)): ?>
        <div class="alert alert-info">You have not added any products yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:0.5rem;"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo safe_stock($product); ?></td>
                            <td><?php echo $product['created_at']; ?></td>
                            <td>
                                <a href="/vehicle-accessory-store/products/edit.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm me-1 text-dark fw-bold">Edit</a>
                                <form method="POST" action="/vehicle-accessory-store/products/delete.php" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm text-light fw-bold" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 