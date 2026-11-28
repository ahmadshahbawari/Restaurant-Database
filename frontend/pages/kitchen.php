<?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Kitchen Board';
require_once __DIR__ . '/../../backend/includes/kitchen.php';
require_once __DIR__ . '/../includes/header.php';
// Kitchen accessible to admin, manager, cashier, waiter, chef, staff
requireRole(['admin', 'manager', 'cashier', 'waiter', 'chef', 'staff']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kitchenId = (int)post('kitchen_order_id');
    $status    = post('kitchen_status');
    updateKitchenStatus($kitchenId, $status)
        ? setFlash('success', 'Kitchen status updated.')
        : setFlash('danger', 'Update failed.');
    redirect('frontend/pages/kitchen.php');
}