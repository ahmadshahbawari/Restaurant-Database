<?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Payments';
require_once __DIR__ . '/../../backend/includes/payment.php';
require_once __DIR__ . '/../../backend/includes/order.php';
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';
requireLogin();
requireRole(['admin', 'manager', 'cashier', 'staff']);

$action  = get('action', 'list');
$orderId = (int)get('order_id', '0');//