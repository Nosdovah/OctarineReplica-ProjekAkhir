<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo_modifier->prepare("DELETE FROM customer_reviews WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Review deleted successfully.";
    } catch (\PDOException $e) {
        $error = "Error deleting review: " . $e->getMessage();
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['customer_name']);
    $text = trim($_POST['review_text']);
    $rating = (int)$_POST['rating'];
    
    if ($name && $text && $rating >= 1 && $rating <= 5) {
        try {
            $stmt = $pdo_modifier->prepare("INSERT INTO customer_reviews (customer_name, review_text, rating) VALUES (?, ?, ?)");
            $stmt->execute([$name, $text, $rating]);
            $success = "Review added successfully.";
        } catch (\PDOException $e) {
            $error = "Error adding review: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all fields correctly.";
    }
}

// Fetch reviews
try {
    $stmt = $pdo_modifier->query("SELECT * FROM customer_reviews ORDER BY id DESC");
    $reviews = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error = "Reviews table not found. Please run migration (migrate_reviews.php).";
    $reviews = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Octarine Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=<?= filemtime('../style.css') ?>">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="dashboard.php" class="sidebar-logo">OCTARINE.</a>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt" style="width: 20px; text-align: center;"></i> Dashboard</a>
                <a href="add.php"><i class="fas fa-box" style="width: 20px; text-align: center;"></i> Add Product</a>
                <a href="categories.php"><i class="fas fa-tags" style="width: 20px; text-align: center;"></i> Categories</a>
                <a href="hero_edit.php"><i class="fas fa-image" style="width: 20px; text-align: center;"></i> Hero Settings</a>
                <a href="mid_banner_edit.php"><i class="fas fa-flag" style="width: 20px; text-align: center;"></i> Mid Banner</a>
                <a href="about_edit.php"><i class="fas fa-address-card" style="width: 20px; text-align: center;"></i> About Page</a>
                <a href="reviews.php" class="active"><i class="fas fa-star" style="width: 20px; text-align: center;"></i> Customer Reviews</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
                <div class="container" style="max-width: 900px;">
                    <div class="header-top">
                        <h1>Customer Reviews</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <!-- Add Review Form -->
                    <div class="form-card" style="margin-bottom: 40px; padding: 30px;">
                        <h3 style="margin-bottom: 20px; font-weight: 600;">Add New Review</h3>
                        <form action="reviews.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Customer Name *</label>
                                    <input type="text" name="customer_name" required placeholder="e.g. Sarah J.">
                                </div>
                                <div class="form-group">
                                    <label>Rating (1-5) *</label>
                                    <select name="rating" required>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Good</option>
                                        <option value="3">3 Stars - Average</option>
                                        <option value="2">2 Stars - Poor</option>
                                        <option value="1">1 Star - Terrible</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Review Content *</label>
                                <textarea name="review_text" rows="3" required placeholder="What did they say?"></textarea>
                            </div>

                            <div class="form-actions" style="margin-top: 20px; padding-top: 0; border: none;">
                                <button type="submit" class="btn-admin">Add Review</button>
                            </div>
                        </form>
                    </div>

                    <!-- Reviews List -->
                    <div class="table-card">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea;">Customer</th>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea;">Rating</th>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea;">Review</th>
                                    <th style="padding: 16px; text-align: center; background: #fafafa; border-bottom: 1px solid #eaeaea;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reviews)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 30px; text-align: center; color: var(--secondary);">No reviews found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $review): ?>
                                    <tr>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; font-weight: 500;"><?= htmlspecialchars($review['customer_name']) ?></td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; color: #fbc02d;">
                                            <?php for($i = 0; $i < $review['rating']; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; color: var(--secondary); max-width: 300px; line-height: 1.5;">"<?= htmlspecialchars($review['review_text']) ?>"</td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; text-align: center;">
                                            <a href="reviews.php?delete=<?= $review['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Are you sure you want to delete this review?')" style="display: inline-flex; text-decoration: none;"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
