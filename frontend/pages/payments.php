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

// ── Handle POST before any output ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = post('action');

    if ($postAction === 'create_payment') {
        $data = [
            'order_id'       => (int)post('order_id'),
            'amount'         => (float)post('amount'),
            'payment_status' => post('payment_status', 'Paid'),
        ];
        createPayment($data) ? setFlash('success','Payment recorded.') : setFlash('danger','Failed to record payment.');
        redirect('frontend/pages/payments.php');

    } elseif ($postAction === 'update_payment_status') {
        $pid    = (int)post('payment_id');
        $status = post('payment_status');
        updatePaymentStatus($pid, $status) ? setFlash('success','Payment status updated.') : setFlash('danger','Update failed.');
        redirect('frontend/pages/payments.php');
    }
}

$payments = getAllPayments();
$newOrder = ($action === 'new' && $orderId) ? getOrderById($orderId) : null;

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'new' && $newOrder): ?>
<div class="card" style="max-width:540px;">
    <div class="card-header">
        <h3> Record Payment — Order #<?= h($newOrder['ORDER_ID']) ?></h3>
        <a href="<?= BASE_URL ?>frontend/pages/payments.php" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <div style="background:var(--light);border-radius:6px;padding:14px;margin-bottom:20px;">
            <div><strong>Customer:</strong> <?= h($newOrder['CUSTOMER_NAME']) ?></div>
            <div><strong>Order Type:</strong> <?= h($newOrder['ORDER_TYPE']) ?></div>
            <div><strong>Total Amount:</strong> <strong style="color:var(--primary)"><?= money((float)$newOrder['TOTAL_AMOUNT']) ?></strong></div>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_payment">
            <input type="hidden" name="order_id" value="<?= (int)$newOrder['ORDER_ID'] ?>">
            <div class="form-group">
                <label>Amount Received ($) *</label>
                <input type="number" name="amount" class="form-control" required step="0.01" min="0"
                       value="<?= h($newOrder['TOTAL_AMOUNT']) ?>">
            </div>
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status" class="form-control">
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success"> Confirm Payment</button>
                <a href="<?= BASE_URL ?>frontend/pages/payments.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<div class="card">
    <div class="card-header">
        <h3> All Payments (<?= count($payments) ?>)</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($payments)): ?>
        <div class="empty-state"><div class="empty-icon"></div><p>No payments recorded yet.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
        <table>
            <thead><tr><th>Payment ID</th><th>Order #</th><th>Customer</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td>#<?= h($p['PAYMENT_ID']) ?></td>
                <td><a href="<?= BASE_URL ?>frontend/pages/orders.php?action=view&id=<?= (int)$p['ORDER_ID'] ?>">#<?= h($p['ORDER_ID']) ?></a></td>
                <td><?= h($p['CUSTOMER_NAME']) ?></td>
                <td><?= h($p['ORDER_TYPE']) ?></td>
                <td><strong><?= money((float)$p['AMOUNT']) ?></strong></td>
                <td><?= statusBadge($p['PAYMENT_STATUS'] === 'Paid' ? 'Completed' : ($p['PAYMENT_STATUS'] === 'Unpaid' ? 'Pending' : 'Cancelled')) ?>
                    <small><?= h($p['PAYMENT_STATUS']) ?></small>
                </td>
                <td><?= h($p['PAYMENT_DATE'] ?? '—') ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:6px;">
                        <input type="hidden" name="action" value="update_payment_status">
                        <input type="hidden" name="payment_id" value="<?= (int)$p['PAYMENT_ID'] ?>">
                        <select name="payment_status" class="form-control" style="width:auto;font-size:12px;padding:5px;">
                            <option value="Paid"     <?= $p['PAYMENT_STATUS']==='Paid'     ? 'selected' : '' ?>>Paid</option>
                            <option value="Unpaid"   <?= $p['PAYMENT_STATUS']==='Unpaid'   ? 'selected' : '' ?>>Unpaid</option>
                            <option value="Refunded" <?= $p['PAYMENT_STATUS']==='Refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">OK</button>
                    </form>
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