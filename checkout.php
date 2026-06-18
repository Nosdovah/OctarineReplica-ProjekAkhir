<?php
session_start();
require_once 'config/db_modifier.php';
require_once 'config/db_viewer.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'];
$total_price = 0;

$placeholders = str_repeat('?,', count($cart_items) - 1) . '?';
$product_ids = array_keys($cart_items);

$stmt = $pdo_viewer->prepare("SELECT * FROM wms_products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $total_price += $product['base_price'] * $cart_items[$product['id']];
}

try {
    $pdo_modifier->beginTransaction();

    // Create order
    $stmt = $pdo_modifier->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$user_id, $total_price]);
    $order_id = $pdo_modifier->lastInsertId();

    // Create order items
    $stmt = $pdo_modifier->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $product) {
        $qty = $cart_items[$product['id']];
        $stmt->execute([$order_id, $product['id'], $qty, $product['base_price']]);
    }

    $pdo_modifier->commit();
    
    // Clear cart
    unset($_SESSION['cart']);
    $success = true;

} catch (Exception $e) {
    $pdo_modifier->rollBack();
    $success = false;
    $error = "Failed to process checkout. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Octarine</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
</head>
<body class="centered-page">
    <div class="checkout-card">
        <?php if ($success): ?>
            <h1 style="color: #1e8e3e;">Order Placed Successfully!</h1>
            <p>Thank you for shopping at Octarine. Your order #<?= $order_id ?> has been received and is currently being processed.</p>
            <a href="index.php" class="btn-continue">Continue Shopping</a>
        <?php else: ?>
            <h1 style="color: #d93025;">Checkout Failed</h1>
            <p><?= htmlspecialchars($error) ?></p>
            <a href="cart.php" class="btn-continue">Back to Cart</a>
        <?php endif; ?>
    </div>
</body>
</html>
