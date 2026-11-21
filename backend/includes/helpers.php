
<?php

// Safely escape output for HTML context.
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
