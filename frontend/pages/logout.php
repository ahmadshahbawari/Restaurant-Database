<?php



define('BASE_URL', '/Restaurant DataBase/');
require_once __DIR__ . '/../../backend/config/session.php';

session_unset();
session_destroy();
header('Location: ' . BASE_URL . 'index.php');
exit;