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
