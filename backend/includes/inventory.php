<?php
require_once __DIR__ . '/../config/db.php';

function getAllInventory(): array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM INVENTORY_ITEM ORDER BY INVENTORY_ID";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}