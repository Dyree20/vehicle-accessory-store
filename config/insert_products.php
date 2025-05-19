<?php
require_once 'database.php';

// First, get the category IDs
$categories = [];
try {
    $stmt = $conn->query("SELECT id, name FROM categories");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[$row['name']] = $row['id'];
    }
} catch(PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Sample products data
$products = [
    [
        'name' => 'LED Headlight Kit',
        'description' => 'High-performance LED headlight conversion kit. Includes 2 LED bulbs, ballasts, and installation guide.',
        'price' => 299.99,
        'category' => 'Lighting',
        'image_path' => '/vehicle-accessory-store/product_images/led-headlight.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Car Floor Mat Set',
        'description' => 'Premium all-weather floor mats. Custom fit for most vehicles. Includes front and rear mats.',
        'price' => 149.99,
        'category' => 'Interior Accessories',
        'image_path' => '/vehicle-accessory-store/product_images/floor-mats.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Chrome Wheel Covers',
        'description' => 'Set of 4 premium chrome wheel covers. Universal fit for 15-17 inch wheels.',
        'price' => 399.99,
        'category' => 'Exterior Accessories',
        'image_path' => '/vehicle-accessory-store/product_images/wheel-covers.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Car Phone Mount',
        'description' => 'Universal smartphone mount with 360° rotation. Compatible with all smartphones.',
        'price' => 49.99,
        'category' => 'Electronics',
        'image_path' => '/vehicle-accessory-store/product_images/phone-mount.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Car Cover',
        'description' => 'Waterproof outdoor car cover. UV resistant and breathable material.',
        'price' => 199.99,
        'category' => 'Exterior Accessories',
        'image_path' => '/vehicle-accessory-store/product_images/car-cover.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Dash Camera',
        'description' => '1080p HD dash camera with night vision and motion detection.',
        'price' => 449.99,
        'category' => 'Electronics',
        'image_path' => '/vehicle-accessory-store/product_images/dash-camera.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Steering Wheel Cover',
        'description' => 'Leather steering wheel cover with anti-slip design. Universal fit.',
        'price' => 79.99,
        'category' => 'Interior Accessories',
        'image_path' => '/vehicle-accessory-store/product_images/steering-cover.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Car Air Freshener',
        'description' => 'Long-lasting car air freshener. Fresh scent that lasts up to 30 days.',
        'price' => 29.99,
        'category' => 'Interior Accessories',
        'image_path' => '/vehicle-accessory-store/product_images/air-freshener.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Tire Pressure Gauge',
        'description' => 'Digital tire pressure gauge with backlit display. Accurate readings.',
        'price' => 39.99,
        'category' => 'Maintenance',
        'image_path' => '/vehicle-accessory-store/product_images/tire-gauge.jpg',
        'user_id' => 1
    ],
    [
        'name' => 'Car Wash Kit',
        'description' => 'Complete car wash kit includes soap, wax, microfiber towels, and wash mitt.',
        'price' => 129.99,
        'category' => 'Maintenance',
        'image_path' => '/vehicle-accessory-store/product_images/wash-kit.jpg',
        'user_id' => 1
    ]
];

try {
    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image_path, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Insert each product
    foreach ($products as $product) {
        // Get the category ID
        $category_id = $categories[$product['category']] ?? null;
        
        if ($category_id === null) {
            echo "Warning: Category '{$product['category']}' not found. Skipping product '{$product['name']}'.\n";
            continue;
        }
        
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $category_id,
            $product['image_path'],
            $product['user_id']
        ]);
    }
    
    echo "Successfully inserted products into the database.";
    
} catch(PDOException $e) {
    echo "Error inserting products: " . $e->getMessage();
}
?> 