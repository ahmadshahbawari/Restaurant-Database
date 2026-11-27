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

$suppliers  = getAllSuppliers();
$editRow    = ($action === 'edit'           && $id) ? getSupplierById($id) : null;
$viewRow    = ($action === 'view'           && $id) ? getSupplierById($id) : null;
$confirmRow = ($action === 'confirm_delete' && $id) ? getSupplierById($id) : null;
$supInv     = ($action === 'view'           && $id) ? getSupplierInventory($id) : [];
$allInv     = ($action === 'view')                  ? getAllInventory() : [];

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($confirmRow): ?>
<div class="card" style="max-width:480px;">
    <div class="card-header"><h3> Confirm Delete</h3></div>
    <div class="card-body">
        <div class="alert alert-warning">
             Delete supplier <strong><?= h($confirmRow['SUPPLIER_NAME']) ?></strong>? This cannot be undone.
        </div>
        <div style="display:flex;gap:10px;">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$confirmRow['SUPPLIER_ID'] ?>">
                <button type="submit" class="btn btn-danger"> Yes, Delete</button>
            </form>
            <a href="<?= BASE_URL ?>frontend/pages/suppliers.php" class="btn btn-outline">Cancel</a>
        </div>
    </div>
</div>

<?php elseif ($action === 'new' || $action === 'edit'): ?>
<div class="card" style="max-width:620px;">
    <div class="card-header">
        <h3><?= $action === 'new' ? ' Add Supplier' : ' Edit Supplier' ?></h3>
        <a href="<?= BASE_URL ?>frontend/pages/suppliers.php" class="btn btn-outline btn-sm"> Back</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
            <?php if ($editRow): ?><input type="hidden" name="id" value="<?= (int)$editRow['SUPPLIER_ID'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" name="supplier_name" class="form-control" required
                       value="<?= h($editRow['SUPPLIER_NAME'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= h($editRow['PHONE'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= h($editRow['EMAIL'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control"><?= h($editRow['ADDRESS'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Active"   <?= (($editRow['STATUS'] ?? 'Active') === 'Active')   ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= (($editRow['STATUS'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"> Save</button>
                <a href="<?= BASE_URL ?>frontend/pages/suppliers.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php elseif ($action === 'view' && $viewRow): ?>
<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    <!-- Supplier details + inventory list -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3> <?= h($viewRow['SUPPLIER_NAME']) ?></h3>
                <div style="display:flex;gap:8px;">
                    <a href="?action=edit&id=<?= (int)$viewRow['SUPPLIER_ID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="<?= BASE_URL ?>frontend/pages/suppliers.php" class="btn btn-outline btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <table style="width:auto;margin-bottom:20px;">
                    <tr>
                        <td style="padding:6px 20px 6px 0;color:var(--muted);white-space:nowrap;">Phone</td>
                        <td><?= h($viewRow['PHONE'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 20px 6px 0;color:var(--muted);">Email</td>
                        <td><?= h($viewRow['EMAIL'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 20px 6px 0;color:var(--muted);">Address</td>
                        <td><?= h($viewRow['ADDRESS'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 20px 6px 0;color:var(--muted);">Status</td>
                        <td><?= statusBadge($viewRow['STATUS'] ?? 'Inactive') ?></td>
                    </tr>
                </table>

                <h4 style="margin-bottom:12px;"> Supplied Inventory Items</h4>
                <?php if (empty($supInv)): ?>
                <p style="color:var(--muted);">No inventory items linked yet. Use the panel on the right to link items.</p>
                <?php else: ?>
                <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Item</th><th>Unit</th><th>Supply Price</th><th>Last Supply Date</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($supInv as $si): ?>
                    <tr>
                        <td><?= h($si['ITEM_NAME']) ?></td>
                        <td><?= h($si['UNIT'] ?? '—') ?></td>
                        <td><?= money((float)$si['SUPPLY_PRICE']) ?></td>
                        <td><?= h($si['LAST_SUPPLY_DATE'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Link inventory panel -->
    <div>
        <div class="card">
            <div class="card-header"><h3> Link Inventory Item</h3></div>
            <div class="card-body">
                <p style="color:var(--muted);font-size:13px;margin-bottom:16px;">
                    Link an inventory item to this supplier with a supply price. If the item is already linked, this will update it.
                </p>
                <form method="POST">
                    <input type="hidden" name="action" value="link_inventory">
                    <input type="hidden" name="supplier_id" value="<?= (int)$viewRow['SUPPLIER_ID'] ?>">
                    <div class="form-group">
                        <label>Inventory Item *</label>
                        <select name="inventory_id" class="form-control" required>
                            <option value="">— Select Item —</option>
                            <?php foreach ($allInv as $inv): ?>
                            <option value="<?= (int)$inv['INVENTORY_ID'] ?>">
                                <?= h($inv['ITEM_NAME'].' ('.$inv['UNIT'].')') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Supply Price ($) *</label>
                        <input type="number" name="supply_price" class="form-control"
                               step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Last Supply Date</label>
                        <input type="date" name="last_supply_date" class="form-control"
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Save Link
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php else: ?>
<!--  LIST  -->
<div class="card">
    <div class="card-header">
        <h3> Suppliers (<?= count($suppliers) ?>)</h3>
        <a href="?action=new" class="btn btn-primary btn-sm">Add Supplier</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($suppliers)): ?>
        <div class="empty-state"><div class="empty-icon"></div><p>No suppliers found.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>ID</th><th>Supplier Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($suppliers as $s): ?>
            <tr>
                <td><?= h($s['SUPPLIER_ID']) ?></td>
                <td><strong><?= h($s['SUPPLIER_NAME']) ?></strong></td>
                <td><?= h($s['PHONE'] ?? '—') ?></td>
                <td><?= h($s['EMAIL'] ?? '—') ?></td>
                <td><?= h($s['ADDRESS'] ?? '—') ?></td>
                <td><?= statusBadge($s['STATUS'] ?? 'Inactive') ?></td>
                <td>
                    <a href="?action=view&id=<?= (int)$s['SUPPLIER_ID'] ?>" class="btn btn-info btn-sm">View</a>
                    <a href="?action=edit&id=<?= (int)$s['SUPPLIER_ID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="?action=confirm_delete&id=<?= (int)$s['SUPPLIER_ID'] ?>" class="btn btn-danger btn-sm">Delete</a>
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