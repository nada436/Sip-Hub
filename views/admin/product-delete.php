<?php
$activePage = 'products';
$searchPlaceholder = 'Search products...';
require_once '../../config/config.php';
require_once '../../db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
$db = DATA_BASE::getInstance();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    header("Location: products.php");
    exit;
}

$result = $db->select("products", "id=$id");
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php?error=Product+not+found");
    exit;
}

    if (!empty($product['image'])) {
        $imgPath = "../../images/" . $product['image'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }
    $db->delete("products", "id=$id");
    header("Location: products.php?success=Product+deleted+successfully");
    exit;


$cssRoot = BASE_URL . '/assets/css';
?>
