<?php
require_once __DIR__ . '/../config/db.php';

function getAllMenuItems(): array {
    $conn = getDBConnection();
    $sql  = "SELECT m.*, c.CATEGORY_NAME
             FROM MENU_ITEM m
             JOIN CATEGORY c ON c.CATEGORY_ID = m.CATEGORY_ID
             ORDER BY m.ITEM_ID";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}