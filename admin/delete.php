<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // First, get the image path to delete the file
    $stmt = $pdo_modifier->prepare("SELECT image_path FROM wms_products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        if ($product['image_path']) {
            $file_path = __DIR__ . '/../uploads/' . $product['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Delete the record using a prepared statement
        $del_stmt = $pdo_modifier->prepare("DELETE FROM wms_products WHERE id = ?");
        $del_stmt->execute([$id]);
    }
}

header("Location: dashboard.php");
exit;
?>
