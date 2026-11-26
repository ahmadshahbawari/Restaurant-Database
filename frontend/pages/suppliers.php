<?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Suppliers';
require_once __DIR__ . '/../../backend/includes/supplier.php';
require_once __DIR__ . '/../../backend/includes/inventory.php';
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';
requireLogin();
requireRole(['admin', 'manager', 'supplier']);

$action = get('action', 'list');
$id     = (int)get('id', '0');


// ── Handle POST before any output ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = post('action');
    $postId     = (int)post('id');

    if ($postAction === 'create' || $postAction === 'update') {
        $data = [
            'supplier_name' => post('supplier_name'),
            'phone'         => post('phone'),
            'email'         => post('email'),
            'address'       => post('address'),
            'status'        => post('status', 'Active'),
        ];
        if ($postAction === 'create') {
            createSupplier($data)          ? setFlash('success','Supplier added.')   : setFlash('danger','Failed to add.');
        } else {
            updateSupplier($postId, $data) ? setFlash('success','Supplier updated.') : setFlash('danger','Update failed.');
        }
        redirect('frontend/pages/suppliers.php');

    } elseif ($postAction === 'delete') {
        deleteSupplier($postId)
            ? setFlash('success','Supplier deleted.')
            : setFlash('danger','Cannot delete — linked to inventory.');
        redirect('frontend/pages/suppliers.php');

    } elseif ($postAction === 'link_inventory') {
        $suppId = (int)post('supplier_id');
        $invId  = (int)post('inventory_id');
        $price  = (float)post('supply_price');
        $date   = post('last_supply_date', date('Y-m-d'));
        upsertSupplierInventory($suppId, $invId, $price, $date)
            ? setFlash('success','Supplier inventory link saved.')
            : setFlash('danger','Failed to save link.');
        redirect('frontend/pages/suppliers.php?action=view&id=' . $suppId);
    }
}