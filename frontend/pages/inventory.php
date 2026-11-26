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

$items      = getAllInventory();
$lowStock   = getLowStockItems();
$editRow    = ($action === 'edit'           && $id) ? getInventoryById($id) : null;
$confirmRow = ($action === 'confirm_delete' && $id) ? getInventoryById($id) : null;

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($lowStock) && $action === 'list'): ?>
<div class="alert alert-warning">
     <strong><?= count($lowStock) ?></strong> item(s) are at or below reorder level:
    <?php foreach ($lowStock as $ls): ?>
        <strong><?= h($ls['ITEM_NAME']) ?></strong> (<?= h($ls['QUANTITY_IN_STOCK']) ?> <?= h($ls['UNIT'] ?? '') ?>)<?= !($ls === end($lowStock)) ? ',' : '' ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>