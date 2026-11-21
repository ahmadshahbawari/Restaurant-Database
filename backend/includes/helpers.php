
<?php

// Safely escape output for HTML context.
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


// Format a number as currency (USD).

function money(float $amount): string {
    return '$' . number_format($amount, 2);
}


// Return a CSS badge class for order/payment/kitchen status.

function statusBadge(string $status): string {
    $map = [
        'Pending'    => 'badge-warning',
        'Active'     => 'badge-success',
        'Inactive'   => 'badge-danger',
        'Completed'  => 'badge-success',
        'Cancelled'  => 'badge-danger',
        'Preparing'  => 'badge-info',
        'Ready'      => 'badge-success',
        'Paid'       => 'badge-success',
        'Unpaid'     => 'badge-danger',
        'Processing' => 'badge-info',
    ];

    $class = $map[$status] ?? 'badge-secondary';

    return '<span class="badge ' . $class . '">' . h($status) . '</span>';
}

// Redirect to a given path and exit.

function redirect(string $path): void {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}


// Return POST value or default.

function post(string $key, string $default = ''): string {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}


// Return GET value or default.

function get(string $key, string $default = ''): string {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// Flash message helpers using session.

function setFlash(string $type, string $msg): void {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_msg']  = $msg;
}

function getFlash(): array {
    if (isset($_SESSION['flash_msg'])) {
        $flash = [
            'type' => $_SESSION['flash_type'],
            'msg' => $_SESSION['flash_msg']
        ];

        unset($_SESSION['flash_type'], $_SESSION['flash_msg']);

        return $flash;
    }

    return [];
}

