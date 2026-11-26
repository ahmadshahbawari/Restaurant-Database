<?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Inventory';
require_once __DIR__ . '/../../backend/includes/inventory.php';
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';
requireLogin();
requireRole(['admin', 'manager', 'supplier']);

$action = get('action', 'list');
$id     = (int)get('id', '0');//