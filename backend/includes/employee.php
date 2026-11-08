<?php
require_once __DIR__ . '/../config/db.php';

function getAllEmployees(): array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM EMPLOYEE ORDER BY EMPLOYEE_ID";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}


function getEmployeeById(int $id): ?array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM EMPLOYEE WHERE EMPLOYEE_ID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':id', $id);
    oci_execute($stmt);
    $row  = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    return $row ?: null;
}

?>