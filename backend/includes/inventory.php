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

function getInventoryById(int $id): ?array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM INVENTORY_ITEM WHERE INVENTORY_ID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':id', $id);
    oci_execute($stmt);
    $row  = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    return $row ?: null;
}