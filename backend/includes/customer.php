<?php
require_once __DIR__ . '/../config/db.php';

function getAllCustomers(): array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM CUSTOMER ORDER BY CUSTOMER_ID";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}