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
             createKitchenOrder($newId);
            setFlash('success', "Order #$newId created and sent to kitchen.");
        } else {
            setFlash('danger', 'Failed to create order.');
        }
        redirect('frontend/pages/orders.php');

    } elseif ($postAction === 'update_status') {
        $oid    = (int)post('order_id');
        $status = post('status');
        updateOrderStatus($oid, $status)
            ? setFlash('success', 'Order status updated.')
            : setFlash('danger',  'Update failed.');
        redirect('frontend/pages/orders.php?action=view&id=' . $oid);

    } elseif ($postAction === 'delete_order') {
        $oid = (int)post('order_id');
        deleteOrder($oid)
            ? setFlash('success', 'Order deleted.')
            : setFlash('danger',  'Failed to delete order.');
        redirect('frontend/pages/orders.php');
    }
}
// ── Load data ──
$orders        = ($action === 'list')                ? getAllOrders()      : [];
$customers     = ($action === 'new')                 ? getAllCustomers()   : [];
$menuItems     = ($action === 'new')                 ? getAllMenuItems()   : [];
$viewOrder     = ($action === 'view'           && $id) ? getOrderById($id) : null;
$confirmOrder  = ($action === 'confirm_delete' && $id) ? getOrderById($id) : null;
$orderItems    = $viewOrder ? getOrderItems($id) : [];
$orderStatuses = ['Pending','Processing','Completed','Cancelled'];

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($confirmOrder): ?>
<!-- 
     DELETE CONFIRM 
  -->
<div class="card" style="max-width:480px;">
    <div class="card-header"><h3> Confirm Delete Order</h3></div>
    <div class="card-body">
        <div class="alert alert-warning">
            
            This cannot be undone.
        </div>
        <div style="display:flex;gap:10px;">
            <form method="POST">
                <input type="hidden" name="action" value="delete_order">
                <input type="hidden" name="order_id" value="<?= (int)$confirmOrder['ORDER_ID'] ?>">
                <button type="submit" class="btn btn-danger"> Yes, Delete Order</button>
            </form>
            <a href="<?= BASE_URL ?>frontend/pages/orders.php" class="btn btn-outline">Cancel</a>
        </div>
    </div>
</div>

<?php elseif ($action === 'new'): ?>
<!-- 
     NEW ORDER FORM 
  -->
<div class="card">
    <div class="card-header">
        <h3> New Order</h3>
        <a href="<?= BASE_URL ?>frontend/pages/orders.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="create_order">

            <div class="form-row">
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">— Select Customer —</option>
                        <?php foreach ($customers as $c): ?>
                        <option value="<?= (int)$c['CUSTOMER_ID'] ?>">
                            <?= h($c['FIRST_NAME'].' '.$c['LAST_NAME']) ?>
                            <?= $c['PHONE'] ? '('.$c['PHONE'].')' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Order Type *</label>
                    <select name="order_type" class="form-control" required>
                        <option value="Dine-In">Dine-In</option>
                        <option value="Takeaway">Takeaway</option>
                        <option value="Delivery">Delivery</option>
                    </select>
                </div>
            </div>

            <hr style="margin:20px 0;">
            <h4 style="margin-bottom:6px;">Order Items</h4>
            <p style="color:var(--muted);font-size:12px;margin-bottom:14px;">
                Fill in the rows you need. Empty rows (no item selected) are ignored automatically.
            </p>

            <!-- Static 10-row item table — no JavaScript needed -->
            <div class="table-wrapper" style="margin-bottom:16px;">
            <table>
                <thead>
                    <tr>
                        <th style="width:35%">Menu Item</th>
                        <th style="width:10%">Qty</th>
                        <th style="width:15%">Unit Price ($)</th>
                        <th>Special Instructions</th>
                    </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < 10; $i++): ?>
                <tr>
                    <td>
                        <select name="item_id[]" class="form-control" style="font-size:13px;">
                            <option value="">— No item —</option>
                            <?php foreach ($menuItems as $m): ?>
                            <?php if ($m['AVAILABILITY'] !== 'Yes') continue; ?>
                            <option value="<?= (int)$m['ITEM_ID'] ?>" data-price="<?= (float)$m['PRICE'] ?>">
                                <?= h($m['ITEM_NAME']) ?> — <?= money((float)$m['PRICE']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control" min="1" value="1">
                    </td>
                    <td>
                        <input type="number" name="unit_price[]" class="form-control" step="0.01" min="0" value="0.00">
                    </td>
                    <td>
                        <input type="text" name="special_instructions[]" class="form-control"
                               placeholder="e.g. No onions">
                    </td>
                </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            </div>

            <div class="alert alert-info">
                ℹ After selecting an item, manually enter the unit price from the menu. The system stores it as entered.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Place Order &amp; Send to Kitchen</button>
                <a href="<?= BASE_URL ?>frontend/pages/orders.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php elseif ($action === 'view' && $viewOrder): ?>
