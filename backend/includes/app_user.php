<?php
require_once __DIR__ . '/../config/db.php';

 

function getAllAppUsers(): array {
    $conn = getDBConnection();
    $sql  = 'SELECT u.USER_ID, u.USERNAME, u.STATUS, u.EMPLOYEE_ID, u.CREATED_DATE,
                    e.FIRST_NAME || \' \' || e.LAST_NAME AS FULL_NAME, e.ROLE
             FROM APP_USER u
             JOIN EMPLOYEE e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
             ORDER BY u.USER_ID';
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $rows[] = $row; // PASSWORD not selected, so nothing to unset
    }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}

?>