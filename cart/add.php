<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];

    try {
        // Check if product exists and get stock
        $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if (!$product) {
            throw new Exception("Product not found");
        }
        if ($product['stock'] == 0) {
            throw new Exception("Product is sold out");
        }

        // Check if product is already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $cart_item = $stmt->fetch();

        if ($cart_item) {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$cart_item['id']]);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product_id]);
        }

        $_SESSION['success'] = "Product added to cart successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to add product to cart: " . $e->getMessage();
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit(); 