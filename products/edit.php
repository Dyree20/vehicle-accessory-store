<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $_SESSION['user_id']]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: ../index.php");
    exit();
}

// Get categories for dropdown
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $image_path = $product['image_path']; // Always keep existing image

    try {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image_path = ?, stock = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $description, $price, $category_id, $image_path, $stock, $product_id, $_SESSION['user_id']]);
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: ../index.php");
        exit();
    } catch(PDOException $e) {
        $error = "Failed to update product: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Vehicle Accessory Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/vehicle-accessory-store/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: #181a1b;
            color: #f1f1f1;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .vehicle-anim-bg {
            position: fixed;
            left: 0; top: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            background: #1a1a1a;
        }
        .vehicle-anim-bg img {
            position: absolute;
            width: 120px;
            height: auto;
            opacity: 0.4;
            will-change: transform;
            filter: brightness(0.8);
        }
        .edit-product-panel {
            position: relative;
            z-index: 2;
            margin: 0 auto;
            max-width: 500px;
            width: 95%;
            background: rgba(36, 36, 36, 0.97);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            border-radius: 2rem;
            padding: 2.5rem 2rem 2rem 2rem;
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
            animation: floatIn 1.1s cubic-bezier(.39,.575,.56,1.000);
            border: 1px solid #333;
            backdrop-filter: blur(12px);
        }
        @keyframes floatIn {
            0% { opacity: 0; transform: translateY(40px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .edit-product-panel h3 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: #e5e7eb;
            letter-spacing: 1px;
            text-align: center;
        }
        .edit-product-panel h3 .accent {
            color: #3ea6ff;
        }
        .edit-product-panel .form-label {
            color: #60b8ff;
            font-weight: 500;
            font-size: 1.1rem;
        }
        .edit-product-panel .form-control, .edit-product-panel .form-select {
            background: #333 !important;
            color: #e5e7eb !important;
            border: 1.5px solid #444;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            padding: 1rem 1.2rem;
        }
        .edit-product-panel .form-control:focus, .edit-product-panel .form-select:focus {
            border-color: #3ea6ff;
            box-shadow: 0 0 0 2px #3ea6ff44;
        }
        .edit-product-panel .btn-primary {
            background: #3ea6ff;
            color: #fff;
            border: none;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            padding: 1rem 0;
            margin-top: 1rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 12px #3ea6ff44;
        }
        .edit-product-panel .btn-primary:hover {
            background: #60b8ff;
            color: #fff;
        }
        .edit-product-panel .btn-secondary {
            background: #444;
            color: #e5e7eb;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 1.2rem;
            padding: 1rem 0;
            margin-right: 0.5rem;
            border: none;
            transition: background 0.2s, color 0.2s;
        }
        .edit-product-panel .btn-secondary:hover {
            background: #555;
            color: #fff;
        }
        .edit-product-panel .alert-danger {
            background: #2d1a1a;
            color: #ffb3b3;
            border: 1px solid #a94442;
            border-radius: 1rem;
        }
        .edit-product-panel .d-grid {
            gap: 0.5rem;
        }
        .current-image {
            width: 100%;
            max-width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>
    <div class="d-flex justify-content-center align-items-center" style="min-height: 90vh;">
        <div class="edit-product-panel">
            <h3><i class="bi bi-pencil-square accent"></i> Edit <span class="accent">Product</span></h3>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3 text-center">
                    <label class="form-label" style="display:block; color:#60b8ff; font-weight:600; margin-bottom:0.5rem;">
                        Current Product Image
                    </label>
                    <img src="<?php echo htmlspecialchars($product['image_path'] ? '/' . ltrim($product['image_path'], '/') : '/vehicle-accessory-store/product_images/default.jpg'); ?>"
                         alt="Current product image"
                         class="current-image"
                         onerror="this.onerror=null;this.src='/vehicle-accessory-store/product_images/default.jpg';">
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo isset($product['stock']) ? (int)$product['stock'] : 0; ?>" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="../index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/vehicle-accessory-store/assets/js/vehicle-animation.js"></script>
</body>
</html> 