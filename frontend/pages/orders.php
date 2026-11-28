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
