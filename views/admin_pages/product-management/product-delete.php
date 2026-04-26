<?php
require_once '../../../db.php';

$db = DATA_BASE::getInstance();
$id = $_GET['id'];

$result  = $db->select("products", "id=$id");
$product = $result->fetch_assoc();

if ($product && !empty($product['image'])) {
    $imgPath = "../../../images/".$product['image'];
    if (file_exists($imgPath)) {
        unlink($imgPath);
    }
}

$db->delete("products", "id=$id");
header("Location: products.php");
exit;
?>