<?php
//  Authentication — reads credentials from APP_USER table.
 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

function loginUser(string $username, string $password) {

    if ($username === '' || $password === '') {
        return false;
    }

    $conn = getDBConnection();

    ?>