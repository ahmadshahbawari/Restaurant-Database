<?php
// header.php — included at top of every authenticated page.
// $pageTitle must be set before including this file.

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Restaurant DataBase/');
}

require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';

requireLogin();   // redirect to login if not authenticated

$flash = getFlash();

// Helper: returns 'active' if $page matches the current script filename.

function active(string $page): string {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>