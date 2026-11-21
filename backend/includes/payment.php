require_once __DIR__ . '/../config/db.php';

function getAllPayments(): array {
    $conn = getDBConnection();
    $sql  = "SELECT p.*, o.TOTAL_AMOUNT, o.ORDER_TYPE,
                    c.FIRST_NAME || ' ' || c.LAST_NAME AS CUSTOMER_NAME
             FROM PAYMENT p
             JOIN ORDERS o ON o.ORDER_ID = p.ORDER_ID
             JOIN CUSTOMER c ON c.CUSTOMER_ID = o.CUSTOMER_ID
             ORDER BY p.PAYMENT_DATE DESC";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) { $rows[] = $row; }
    oci_free_statement($stmt);
    oci_close($conn);
    return $rows;
}