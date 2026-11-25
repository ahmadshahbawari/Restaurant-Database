<?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Orders';
require_once __DIR__ . '/../../backend/includes/order.php';
require_once __DIR__ . '/../../backend/includes/customer.php';
require_once __DIR__ . '/../../backend/includes/menu_item.php';
require_once __DIR__ . '/../../backend/includes/kitchen.php';
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';
requireLogin();
requireRole(['admin', 'manager', 'cashier', 'waiter', 'staff']);

$action = get('action', 'list');
$id     = (int)get('id', '0');

// ── Handle POST before any output ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = post('action');

    if ($postAction === 'create_order') {
        $newId = createOrder([
            'customer_id' => (int)post('customer_id'),
            'employee_id' => (int)$_SESSION['employee_id'],
            'order_type'  => post('order_type'),
        ]);
        if ($newId > 0) {
            $itemIds = $_POST['item_id']              ?? [];
            $qtys    = $_POST['quantity']             ?? [];
            $prices  = $_POST['unit_price']           ?? [];
            $notes   = $_POST['special_instructions'] ?? [];
            for ($i = 0; $i < count($itemIds); $i++) {
                if (empty($itemIds[$i]) || empty($qtys[$i])) continue;
                addOrderItem($newId, [
                    'item_id'              => (int)$itemIds[$i],
                    'quantity'             => (int)$qtys[$i],
                    'unit_price'           => (float)$prices[$i],
                    'special_instructions' => $notes[$i] ?? '',
                ]);
            }
