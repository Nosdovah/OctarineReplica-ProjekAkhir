<?php
session_start();
require_once 'config/db_viewer.php';

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $product_ids = array_keys($_SESSION['cart']);

    $stmt = $pdo_viewer->prepare("SELECT * FROM wms_products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['base_price'] * $quantity;
        $total_price += $subtotal;
        $product['cart_quantity'] = $quantity;
        $product['subtotal'] = $subtotal;
        $cart_items[] = $product;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Octarine</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-section {
            padding: 80px 0;
            min-height: 70vh;
        }

        .cart-header {
            margin-bottom: 40px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .cart-table th,
        .cart-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #eaeaea;
        }

        .cart-table th {
            background: #fafafa;
            color: var(--secondary);
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .product-col {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .product-col img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            background: #f0f0f0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: #e0e0e0;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-delete {
            color: #d93025;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .cart-summary {
            background: #fafafa;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #eaeaea;
            max-width: 400px;
            margin-left: auto;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 18px;
            font-weight: 600;
        }

        .btn-checkout {
            display: block;
            width: 100%;
            background: var(--main);
            color: var(--white);
            text-align: center;
            padding: 14px;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 24px;
            transition: var(--transition);
        }

        .btn-checkout:hover {
            background: var(--secondary);
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="container navbar">
            <a href="index.php" class="logo">OCTARINE</a>
            <nav class="nav-links">
                <a href="index.php#shop">Shop</a>
                <a href="index.php#about">About</a>
            </nav>
            <div class="nav-utils">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="font-weight: 600; margin-right: 15px;">Hi,
                        <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="logout.php" class="login-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="login-link">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="cart-section container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
        </div>

        <?php if (empty($cart_items)): ?>
            <p style="text-align: center; font-size: 18px; color: var(--secondary); margin-top: 40px;">Your cart is empty.
                <a href="index.php#shop" style="color: var(--main); font-weight: 600;">Go shopping!</a></p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr data-id="<?= $item['id'] ?>">
                            <td>
                                <div class="product-col">
                                    <?php if ($item['image_path']): ?>
                                        <img src="uploads/<?= htmlspecialchars($item['image_path']) ?>" alt="Product">
                                    <?php else: ?>
                                        <div
                                            style="width:60px; height:60px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; border-radius:4px;">
                                            <i class="fas fa-box" style="color:#ccc;"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($item['name']) ?></div>
                                        <div style="font-size: 13px; color: var(--secondary);">
                                            <?= htmlspecialchars($item['brand']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>Rp <?= number_format($item['base_price'], 0, ',', '.') ?></td>
                            <td>
                                <div class="qty-controls">
                                    <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">-</button>
                                    <input type="number" class="qty-input" value="<?= $item['cart_quantity'] ?>"
                                        onchange="setQty(<?= $item['id'] ?>, this.value)" min="1">
                                    <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
                                </div>
                            </td>
                            <td style="font-weight: 600;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                            <td>
                                <button class="btn-delete" onclick="deleteItem(<?= $item['id'] ?>)"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Total:</span>
                    <span>Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                </div>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function updateQty(productId, change) {
            const input = document.querySelector(`tr[data-id="${productId}"] .qty-input`);
            let newQty = parseInt(input.value) + change;
            if (newQty < 1) newQty = 1;
            setQty(productId, newQty);
        }

        function setQty(productId, qty) {
            if (qty < 1) return;
            fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update&product_id=${productId}&quantity=${qty}`
            }).then(res => res.json()).then(data => {
                if (data.success) location.reload();
            });
        }

        function deleteItem(productId) {
            if (!confirm('Remove this item from your cart?')) return;
            fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&product_id=${productId}`
            }).then(res => res.json()).then(data => {
                if (data.success) location.reload();
            });
        }
    </script>
</body>

</html>