<?php
// views/user_pages/cart_action.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

$db      = DATA_BASE::getInstance();
$user_id = 1; 

$action     = $_POST['action']     ?? ($_GET['action'] ?? '');
$product_id = (int)($_POST['product_id'] ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));

// ── Helper: build full cart array ────────────────────────────
function buildCart($db, $user_id) {
    $res   = $db->selectAll('cart', "user_id = $user_id");
    $items = [];
    $total_qty  = 0;
    $total_price = 0.0;

    while ($row = $res->fetch_assoc()) {
        $prod = $db->select('products', "id = {$row['product_id']}")->fetch_assoc();
        if (!$prod) continue;
        $subtotal     = (float)$prod['price'] * (int)$row['quantity'];
        $total_qty   += (int)$row['quantity'];
        $total_price += $subtotal;
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'name'       => $prod['name'],
            'image'    => $prod ? $prod['image']  : '',
            'price'      => (float)$prod['price'],
            'quantity'   => (int)$row['quantity'],
            'subtotal'   => $subtotal,
        ];
    }
    return ['items' => $items, 'total_qty' => $total_qty, 'total_price' => $total_price];
}

switch ($action) {

    case 'add':
        $existing = $db->select('cart', "user_id=$user_id AND product_id=$product_id")->fetch_assoc();
        if ($existing) {
            $new_qty = (int)$existing['quantity'] + $quantity;
            $db->update('cart', "quantity=$new_qty", "user_id=$user_id AND product_id=$product_id");
        } else {
            $db->insert('cart', 'user_id,product_id,quantity', "$user_id,$product_id,$quantity");
        }
        echo json_encode(['ok' => true] + buildCart($db, $user_id));
        break;

    case 'update':
        if ($quantity < 1) {
            $db->delete('cart', "user_id=$user_id AND product_id=$product_id");
        } else {
            $db->update('cart', "quantity=$quantity", "user_id=$user_id AND product_id=$product_id");
        }
        echo json_encode(['ok' => true] + buildCart($db, $user_id));
        break;

    case 'remove':
        $db->delete('cart', "user_id=$user_id AND product_id=$product_id");
        echo json_encode(['ok' => true] + buildCart($db, $user_id));
        break;

    case 'get':
        echo json_encode(['ok' => true] + buildCart($db, $user_id));
        break;

    case 'place_order':
        $cart = buildCart($db, $user_id);
        if (empty($cart['items'])) {
            echo json_encode(['ok' => false, 'msg' => 'Cart is empty']);
            break;
        }
        $total     = $cart['total_price'];
        $order_id  = $db->insert('orders', 'user_id,total_price,status', "$user_id,$total,'processing'");
        foreach ($cart['items'] as $item) {
            $pid   = $item['product_id'];
            $qty   = $item['quantity'];
            $price = $item['price'];
            $db->insert('order_items', 'order_id,product_id,quantity,price', "$order_id,$pid,$qty,$price");
        }
        $db->delete('cart', "user_id=$user_id");
        echo json_encode(['ok' => true, 'order_id' => $order_id, 'total' => $total]);
        break;

    default:
        echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
}