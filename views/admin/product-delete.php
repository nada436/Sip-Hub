<?php
require_once '../../db.php';
$id=$_GET['id'];
$db = DATA_BASE::getInstance();
$result   = $db->delete("products","id=$id");  
header("Location:products.php");
exit;
?>