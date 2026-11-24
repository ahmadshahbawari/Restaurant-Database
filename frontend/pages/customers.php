[11/24, 3:15 PM] سنګین صاحب: <?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Customers';
require_once __DIR__ . '/../../backend/includes/customer.php';
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';
requireLogin();
requireRole(['admin', 'manager', 'cashier', 'waiter']);

$action = get('action', 'list');
$id     = (int)get('id', '0');
[11/24, 3:22 PM] سنګین صاحب: <?php
define('BASE_URL', '/Restaurant DataBase/');
$pageTitle = 'Customers';
require_once __DIR__ . '/../../backend/includes/customer.php';
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';
requireLogin();
requireRole(['admin', 'manager', 'cashier', 'waiter']);

$action = get('action', 'list');
$id     = (int)get('id', '0');

// ── Handle POST before any output ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => post('first_name'),
        'last_name'  => post('last_name'),
        'phone'      => post('phone'),
        'email'      => post('email'),
        'address'    => post('address'),
    ];
    $postAction = post('action');
    $postId     = (int)post('id');

    if ($postAction === 'create') {
        createCustomer($data)          ? setFlash('success','Customer added.')   : setFlash('danger','Failed to add.');
    } elseif ($postAction === 'update') {
        updateCustomer($postId, $data) ? setFlash('success','Customer updated.') : setFlash('danger','Update failed.');
    } elseif ($postAction === 'delete') {
        deleteCustomer($postId)        ? setFlash('success','Customer deleted.') : setFlash('danger','Cannot delete — has orders.');
    }
    redirect('frontend/pages/customers.php');
}

$search     = get('q');
$customers  = $search ? searchCustomers($search) : getAllCustomers();
$editRow    = ($action === 'edit'           && $id) ? getCustomerById($id) : null;
$confirmRow = ($action === 'confirm_delete' && $id) ? getCustomerById($id) : null;

require_once __DIR__ . '/../includes/header.php';
?>