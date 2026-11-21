
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


