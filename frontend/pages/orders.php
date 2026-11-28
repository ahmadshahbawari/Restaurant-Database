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
<!-- 
     ORDER DETAIL 
  -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

    <div>
        <!-- Receipt-style card -->
        <div class="card">
            <div class="card-header">
                <h3> Order #<?= h($viewOrder['ORDER_ID']) ?></h3>
                <div style="display:flex;gap:8px;">
                    <a href="<?= BASE_URL ?>frontend/pages/orders.php" class="btn btn-outline btn-sm no-print">← Back</a>
                </div>
            </div>
            <div class="card-body">
                <!-- Order meta -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;background:var(--light);padding:14px;border-radius:6px;">
                    <div><span style="color:var(--muted);font-size:12px;">CUSTOMER</span><br><strong><?= h($viewOrder['CUSTOMER_NAME']) ?></strong></div>
                    <div><span style="color:var(--muted);font-size:12px;">SERVED BY</span><br><strong><?= h($viewOrder['EMPLOYEE_NAME']) ?></strong></div>
                    <div><span style="color:var(--muted);font-size:12px;">ORDER TYPE</span><br><strong><?= h($viewOrder['ORDER_TYPE']) ?></strong></div>
                    <div><span style="color:var(--muted);font-size:12px;">DATE</span><br><strong><?= h($viewOrder['ORDER_DATE']) ?></strong></div>
                    <div><span style="color:var(--muted);font-size:12px;">STATUS</span><br><?= statusBadge($viewOrder['STATUS']) ?></div>
                </div>

                <!-- Items table -->
                <h4 style="margin-bottom:10px;">Items Ordered</h4>
                <?php if (empty($orderItems)): ?>
                <p style="color:var(--muted);">No items recorded for this order.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr><th>#</th><th>Item</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                    <?php $lineNum = 1; foreach ($orderItems as $oi): ?>
                    <tr>
                        <td><?= $lineNum++ ?></td>
                        <td><?= h($oi['ITEM_NAME']) ?></td>
                        <td><?= h($oi['QUANTITY']) ?></td>
                        <td><?= money((float)$oi['UNIT_PRICE']) ?></td>
                        <td><?= money((float)$oi['SUBTOTAL']) ?></td>
                        <td style="color:var(--muted);font-size:12px;"><?= h($oi['SPECIAL_INSTRUCTIONS'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:var(--light);font-size:15px;">
                        <td colspan="4" style="text-align:right;"><strong>Total</strong></td>
                        <td colspan="2"><strong style="color:var(--primary)"><?= money((float)$viewOrder['TOTAL_AMOUNT']) ?></strong></td>
                    </tr>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions panel -->
    <div>
        <!-- Status update -->
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><h3>Update Status</h3></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?= (int)$viewOrder['ORDER_ID'] ?>">
                    <div class="form-group">
                        <label>Order Status</label>
                        <select name="status" class="form-control">
                            <?php foreach ($orderStatuses as $s): ?>
                            <option value="<?= h($s) ?>" <?= $viewOrder['STATUS'] === $s ? 'selected' : '' ?>>
                                <?= h($s) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                         Save Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick links -->
        <div class="card">
            <div class="card-header"><h3> Quick Links</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <a href="<?= BASE_URL ?>frontend/pages/payments.php?action=new&order_id=<?= (int)$viewOrder['ORDER_ID'] ?>"
                   class="btn btn-success" style="justify-content:center;">
                     Record Payment
                </a>
                <a href="<?= BASE_URL ?>frontend/pages/kitchen.php" class="btn btn-warning" style="justify-content:center;">
                     Kitchen Board
                </a>
                <a href="<?= BASE_URL ?>frontend/pages/orders.php?action=confirm_delete&id=<?= (int)$viewOrder['ORDER_ID'] ?>"
                   class="btn btn-danger" style="justify-content:center;">
                     Delete Order
                </a>
            </div>
        </div>
    </div>

</div>

<?php else: ?>
<!-- 
     ORDER LIST 
  -->
<div class="card">
    <div class="card-header">
        <h3>All Orders (<?= count($orders) ?>)</h3>
        <a href="?action=new" class="btn btn-primary btn-sm"> New Order</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-icon"></div>
            <p>No orders found. <a href="?action=new">Create the first order.</a></p>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Order #</th><th>Customer</th><th>Served By</th><th>Type</th>
                    <th>Date</th><th>Total</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong>#<?= h($o['ORDER_ID']) ?></strong></td>
                <td><?= h($o['CUSTOMER_NAME']) ?></td>
                <td><?= h($o['EMPLOYEE_NAME']) ?></td>
                <td><?= h($o['ORDER_TYPE']) ?></td>
                <td><?= h($o['ORDER_DATE']) ?></td>
                <td><strong><?= money((float)$o['TOTAL_AMOUNT']) ?></strong></td>
                <td><?= statusBadge($o['STATUS']) ?></td>
                <td>
                    <a href="?action=view&id=<?= (int)$o['ORDER_ID'] ?>" class="btn btn-info btn-sm"> View</a>
                    <a href="?action=confirm_delete&id=<?= (int)$o['ORDER_ID'] ?>" class="btn btn-danger btn-sm">Delete</a>
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
