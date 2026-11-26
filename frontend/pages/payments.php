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