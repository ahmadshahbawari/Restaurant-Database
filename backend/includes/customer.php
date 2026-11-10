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

function getCustomerById(int $id): ?array { 

   $conn = getDBConnection(); 

   $sql  = "SELECT * FROM CUSTOMER WHERE CUSTOMER_ID = :id"; 

   $stmt = oci_parse($conn, $sql); 

   oci_bind_by_na<?php 

require_once __DIR__ . '/../config/db.php'; 

function getAllCustomers(): array { 

   $conn = getDBConnection(); 

   $sql  = "SELECT * FROM CUSTOMER ORDER BY CUSTOMER_ID"; 

   $stmt = oci_parse($conn, $sql); 

   oci_execute($stmt); 

   $rows = []; 

   while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; } 

   oci_free_statement($stmt); 

   oci_close($conn); 

   return $rows; 

} 

me($stmt, ':id', $id); 

   oci_execute($stmt); 

   $row  = oci_fetch_assoc($stmt); 

   oci_free_statement($stmt); 

   oci_close($conn); 

   return $row ?: null; 

}
