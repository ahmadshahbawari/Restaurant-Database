<?php

// index.php — Login Page (entry point)

define('BASE_URL', '/Restaurant DataBase/');

require_once __DIR__ . '/backend/config/session.php';
require_once __DIR__ . '/backend/includes/auth.php';
require_once __DIR__ . '/backend/includes/helpers.php';

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'frontend/pages/dashboard.php');
    exit;
}