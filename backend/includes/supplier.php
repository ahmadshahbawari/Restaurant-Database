<?php
require_once __DIR__ . '/../config/db.php';

function getAllSuppliers(): array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM SUPPLIER ORDER BY SUPPLIER_ID";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}

function getSupplierById(int $id): ?array {
    $conn = getDBConnection();
    $sql  = "SELECT * FROM SUPPLIER WHERE SUPPLIER_ID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':id', $id);
    oci_execute($stmt);
    $row  = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    return $row ?: null;
}

function createSupplier(array $d): bool {
    $conn = getDBConnection();
    $sql  = "INSERT INTO SUPPLIER (SUPPLIER_ID, SUPPLIER_NAME, PHONE, EMAIL, ADDRESS, STATUS)
             VALUES (SUPPLIER_SEQ.NEXTVAL, :sn, :ph, :em, :ad, :st)";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':sn', $d['supplier_name']);
    oci_bind_by_name($stmt, ':ph', $d['phone']);
    oci_bind_by_name($stmt, ':em', $d['email']);
    oci_bind_by_name($stmt, ':ad', $d['address']);
    oci_bind_by_name($stmt, ':st', $d['status']);
    $ok = oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    return (bool)$ok;
}

function updateSupplier(int $id, array $d): bool {
    $conn = getDBConnection();
    $sql  = "UPDATE SUPPLIER SET SUPPLIER_NAME=:sn, PHONE=:ph, EMAIL=:em, ADDRESS=:ad, STATUS=:st
             WHERE SUPPLIER_ID=:id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':sn', $d['supplier_name']);
    oci_bind_by_name($stmt, ':ph', $d['phone']);
    oci_bind_by_name($stmt, ':em', $d['email']);
    oci_bind_by_name($stmt, ':ad', $d['address']);
    oci_bind_by_name($stmt, ':st', $d['status']);
    oci_bind_by_name($stmt, ':id', $id);
    $ok = oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    return (bool)$ok;
}

function deleteSupplier(int $id): bool {
    $conn = getDBConnection();
    $sql  = "DELETE FROM SUPPLIER WHERE SUPPLIER_ID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':id', $id);
    $ok = oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    return (bool)$ok;
}

function getSupplierInventory(int $supplierId): array {
    $conn = getDBConnection();
    $sql  = "SELECT si.*, i.ITEM_NAME, i.UNIT
             FROM SUPPLIER_INVENTORY si
             JOIN INVENTORY_ITEM i ON i.INVENTORY_ID = si.INVENTORY_ID
             WHERE si.SUPPLIER_ID = :sid";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':sid', $supplierId);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}