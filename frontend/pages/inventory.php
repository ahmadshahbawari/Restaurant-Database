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

<?php if ($confirmRow): ?>
<div class="card" style="max-width:480px;">
    <div class="card-header"><h3> Confirm Delete</h3></div>
    <div class="card-body">
        <div class="alert alert-warning">
             Delete inventory item <strong><?= h($confirmRow['ITEM_NAME']) ?></strong>? This cannot be undone.
        </div>
        <div style="display:flex;gap:10px;">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$confirmRow['INVENTORY_ID'] ?>">
                <button type="submit" class="btn btn-danger"> Yes, Delete</button>
            </form>
            <a href="<?= BASE_URL ?>frontend/pages/inventory.php" class="btn btn-outline">Cancel</a>
        </div>
    </div>
</div>

<?php elseif ($action === 'new' || $action === 'edit'): ?>
<div class="card" style="max-width:620px;">
    <div class="card-header">
        <h3><?= $action === 'new' ? ' Add Inventory Item' : ' Edit Inventory Item' ?></h3>
        <a href="<?= BASE_URL ?>frontend/pages/inventory.php" class="btn btn-outline btn-sm"> Back</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
            <?php if ($editRow): ?><input type="hidden" name="id" value="<?= (int)$editRow['INVENTORY_ID'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" class="form-control" required
                           value="<?= h($editRow['ITEM_NAME'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Unit (kg, litre, pcs…)</label>
                    <input type="text" name="unit" class="form-control"
                           value="<?= h($editRow['UNIT'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label>Qty In Stock</label>
                    <input type="number" name="quantity_in_stock" class="form-control" step="0.01" min="0"
                           value="<?= h($editRow['QUANTITY_IN_STOCK'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input type="number" name="reorder_level" class="form-control" step="0.01" min="0"
                           value="<?= h($editRow['REORDER_LEVEL'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label>Unit Cost ($)</label>
                    <input type="number" name="unit_cost" class="form-control" step="0.01" min="0"
                           value="<?= h($editRow['UNIT_COST'] ?? '0') ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?= BASE_URL ?>frontend/pages/inventory.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<div class="card">
    <div class="card-header">
        <h3>Inventory Items (<?= count($items) ?>)</h3>
        <a href="?action=new" class="btn btn-primary btn-sm"> Add Item</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($items)): ?>
        <div class="empty-state"><div class="empty-icon"></div><p>No inventory items.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Item Name</th><th>Unit</th><th>In Stock</th>
                    <th>Reorder At</th><th>Unit Cost</th><th>Stock Status</th><th>Updated</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $inv): ?>
            <?php $isLow = ($inv['QUANTITY_IN_STOCK'] <= $inv['REORDER_LEVEL']); ?>
            <tr style="<?= $isLow ? 'background:rgba(231,76,60,.04);' : '' ?>">
                <td><?= h($inv['INVENTORY_ID']) ?></td>
                <td><strong><?= h($inv['ITEM_NAME']) ?></strong></td>
                <td><?= h($inv['UNIT'] ?? '—') ?></td>
                <td style="<?= $isLow ? 'color:var(--danger);font-weight:700;' : '' ?>">
                    <?= h($inv['QUANTITY_IN_STOCK']) ?>
                </td>
                <td><?= h($inv['REORDER_LEVEL']) ?></td>
                <td><?= money((float)($inv['UNIT_COST'] ?? 0)) ?></td>
                <td>
                    <?= $isLow
                        ? '<span class="badge badge-danger"> Low Stock</span>'
                        : '<span class="badge badge-success">✓ OK</span>' ?>
                </td>
                <td><?= h($inv['LAST_UPDATED'] ?? '—') ?></td>
                <td>
                    <a href="?action=edit&id=<?= (int)$inv['INVENTORY_ID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="?action=confirm_delete&id=<?= (int)$inv['INVENTORY_ID'] ?>" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>