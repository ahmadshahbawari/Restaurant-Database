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



// ── Handle POST before any output ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'item_name'         => post('item_name'),
        'unit'              => post('unit'),
        'quantity_in_stock' => (float)post('quantity_in_stock'),
        'reorder_level'     => (float)post('reorder_level'),
        'unit_cost'         => (float)post('unit_cost'),
    ];
    $postAction = post('action');
    $postId     = (int)post('id');

    if ($postAction === 'create') {
        createInventoryItem($data)          ? setFlash('success','Item added.')   : setFlash('danger','Failed to add.');
    } elseif ($postAction === 'update') {
        updateInventoryItem($postId, $data) ? setFlash('success','Item updated.') : setFlash('danger','Update failed.');
    } elseif ($postAction === 'delete') {
        deleteInventoryItem($postId)        ? setFlash('success','Item deleted.') : setFlash('danger','Cannot delete — supplier linked.');
    }
    redirect('frontend/pages/inventory.php');
}