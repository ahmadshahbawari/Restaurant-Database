//
<?php

define('BASE_URL', '/Restaurant DataBase/');

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../../backend/config/db.php';

require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();