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

    ?>