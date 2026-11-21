
<?php

// Safely escape output for HTML context.
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


// Format a number as currency (USD).

function money(float $amount): string {
    return '$' . number_format($amount, 2);
}