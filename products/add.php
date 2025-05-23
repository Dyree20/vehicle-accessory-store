<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
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
    $user_id = $_SESSION['user_id'];
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    // Handle file upload
    $target_dir = "../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_path = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_file = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Check file size (5MB max)
            if ($_FILES["image"]["size"] <= 5000000) {
                // Allow certain file formats
                if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = str_replace("../", "", $target_file);
                    }
                }
            }
        }
    }

    if ($image_path) {
        try {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image_path, user_id, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category_id, $image_path, $user_id, $stock]);
            $_SESSION['success'] = "Product added successfully!";
            header("Location: ../index.php");
            exit();
        } catch(PDOException $e) {
            $error = "Failed to add product: " . $e->getMessage();
        }
    } else {
        $error = "Failed to upload image";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Vehicle Accessory Store</title>
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
        .add-product-panel {
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
        .add-product-panel h3 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: #e5e7eb;
            letter-spacing: 1px;
            text-align: center;
        }
        .add-product-panel h3 .accent {
            color: #3ea6ff;
        }
        .add-product-panel .form-label {
            color: #60b8ff;
            font-weight: 500;
            font-size: 1.1rem;
        }
        .add-product-panel .form-control, .add-product-panel .form-select {
            background: #333 !important;
            color: #e5e7eb !important;
            border: 1.5px solid #444;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            padding: 1rem 1.2rem;
        }
        .add-product-panel .form-control:focus, .add-product-panel .form-select:focus {
            border-color: #3ea6ff;
            box-shadow: 0 0 0 2px #3ea6ff44;
        }
        .add-product-panel .btn-primary {
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
        .add-product-panel .btn-primary:hover {
            background: #60b8ff;
            color: #fff;
        }
        .add-product-panel .btn-secondary {
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
        .add-product-panel .btn-secondary:hover {
            background: #555;
            color: #fff;
        }
        .add-product-panel .alert-danger {
            background: #2d1a1a;
            color: #ffb3b3;
            border: 1px solid #a94442;
            border-radius: 1rem;
        }
        .add-product-panel .d-grid {
            gap: 0.5rem;
        }
        @media (max-width: 900px) {
            .add-product-panel {
                margin: 1rem auto;
                max-width: 98vw;
                border-radius: 1.2rem;
                padding: 2rem 1rem 1.5rem 1rem;
            }
        }
        .home-btn {
            position: fixed;
            top: 2rem;
            right: 3rem;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="vehicle-anim-bg">
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com.svg" class="vehicle1" alt="Animated Car" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (1).svg" class="vehicle2" alt="Animated Car 2" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (2).svg" class="vehicle3" alt="Animated Car 3" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (3).svg" class="vehicle4" alt="Animated Car 4" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com.svg" class="vehicle5" alt="Animated Car 5" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (1).svg" class="vehicle6" alt="Animated Car 6" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (2).svg" class="vehicle7" alt="Animated Car 7" />
        <img src="/vehicle-accessory-store/assets/images/car-svgrepo-com (3).svg" class="vehicle8" alt="Animated Car 8" />
    </div>
    <div class="d-flex justify-content-end mb-3">
        <a href="/vehicle-accessory-store/index.php" class="btn home-btn">
            <i class="bi bi-house-door-fill me-2"></i> Home
        </a>
    </div>
    <div class="d-flex justify-content-center align-items-center" style="min-height: 90vh;">
        <div class="add-product-panel">
            <h3><i class="bi bi-car-front-fill accent"></i> Add <span class="accent">New Product</span></h3>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="0" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="../index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/vehicle-accessory-store/assets/js/vehicle-animation.js"></script>
</body>
</html> 