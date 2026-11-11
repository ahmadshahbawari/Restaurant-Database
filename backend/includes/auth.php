<?php
//  Authentication — reads credentials from APP_USER table.
 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

function loginUser(string $username, string $password) {

    if ($username === '' || $password === '') {
        return false;
    }

    $conn = getDBConnection();




     /* Select the user — "PASSWORD" must stay double-quoted */
    $sql = 'SELECT u.USER_ID,
                   u.USERNAME,
                   u."PASSWORD"    AS USER_PASSWORD,
                   u.STATUS,
                   u.EMPLOYEE_ID,
                   e.ROLE,
                   e.FIRST_NAME || \' \' || e.LAST_NAME AS FULL_NAME
            FROM   APP_USER u
            LEFT JOIN EMPLOYEE e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
            WHERE  UPPER(u.USERNAME) = UPPER(:uname)';

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':uname', $username);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    oci_close($conn);



     if (!$row) {
        return false; // username not found
    }

    /* STATUS must be exactly 'Active' */
    if (trim((string)($row['STATUS'] ?? '')) !== 'Active') {
        return false;
    }

    /*
     * The column is aliased as USER_PASSWORD to avoid any reserved-word
     * ambiguity when OCI8 returns the result set keys.
     */
    $stored = trim((string)($row['USER_PASSWORD'] ?? ''));

    if ($stored === '') {
        return false; // no password stored
    }

    $valid = false;

    /* 1 — bcrypt hash ($2y$... or $2a$...) */
    if (!$valid && strlen($stored) >= 60 && ($stored[0] === '$')) {
        $valid = password_verify($password, $stored);
    }

    /* 2 — plain text exact match */
    if (!$valid && $stored === $password) {
        $valid = true;
    }

    /* 3 — plain text case-insensitive */
    if (!$valid && strtolower($stored) === strtolower($password)) {
        $valid = true;
    }

    /* 4 — MD5 */
    if (!$valid && strlen($stored) === 32) {
        $valid = ($stored === md5($password));
    }

    /* 5 — SHA1 */
    if (!$valid && strlen($stored) === 40) {
        $valid = ($stored === sha1($password));
    }

    if (!$valid) {
        return false;
    }

    return [
        'user_id'     => $row['USER_ID'],
        'username'    => $row['USERNAME'],
        'employee_id' => $row['EMPLOYEE_ID'] ?? 0,
        'role'        => trim((string)($row['ROLE']      ?? 'Staff')),
        'full_name'   => trim((string)($row['FULL_NAME'] ?? $row['USERNAME'])),
    ];
}

function logoutUser(): void {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}


    ?>