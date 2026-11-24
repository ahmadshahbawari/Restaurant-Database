//
<?php

define('BASE_URL', '/Restaurant DataBase/');

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../../backend/config/db.php';

require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Stat: total customers
$r1 = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM CUSTOMER");
oci_execute($r1);
$s1 = oci_fetch_assoc($r1);
oci_free_statement($r1);


// Stat: total orders today
$r2 = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM ORDERS WHERE TRUNC(ORDER_DATE)=TRUNC(SYSDATE)");
oci_execute($r2);
$s2 = oci_fetch_assoc($r2);
oci_free_statement($r2);


// Stat: today's revenue
$r3 = oci_parse($conn, "SELECT NVL(SUM(AMOUNT),0) AS TOT FROM PAYMENT WHERE TRUNC(PAYMENT_DATE)=TRUNC(SYSDATE) AND PAYMENT_STATUS='Paid'");
oci_execute($r3);
$s3 = oci_fetch_assoc($r3);
oci_free_statement($r3);


// Stat: low stock items
$r4 = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM INVENTORY_ITEM WHERE QUANTITY_IN_STOCK <= REORDER_LEVEL");
oci_execute($r4);
$s4 = oci_fetch_assoc($r4);
oci_free_statement($r4);


// Recent orders (last 10)
$r5 = oci_parse($conn, "SELECT o.ORDER_ID, o.ORDER_TYPE, o.STATUS, o.TOTAL_AMOUNT,
                                TO_CHAR(o.ORDER_DATE,'DD-Mon-YYYY HH24:MI') AS ORDER_DATE,
                                c.FIRST_NAME||' '||c.LAST_NAME AS CUSTOMER_NAME
                         FROM ORDERS o
                         JOIN CUSTOMER c ON c.CUSTOMER_ID=o.CUSTOMER_ID
                         ORDER BY o.ORDER_DATE DESC
                         FETCH FIRST 10 ROWS ONLY");

oci_execute($r5);

$recentOrders = [];

while ($row = oci_fetch_assoc($r5)) {
    $recentOrders[] = $row;
}

oci_free_statement($r5);


// Kitchen pending
$r6 = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM KITCHEN_ORDER WHERE KITCHEN_STATUS NOT IN ('Ready','Cancelled')");
oci_execute($r6);
$s6 = oci_fetch_assoc($r6);
oci_free_statement($r6);


// Close database connection
oci_close($conn);
<!-- Stat Cards -->
<div class="stats-grid">

    <div class="stat-card">
        <div>
            <div class="stat-value"><?= (int)$s1['CNT'] ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value"><?= (int)$s2['CNT'] ?></div>
            <div class="stat-label">Orders Today</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value"><?= money((float)$s3['TOT']) ?></div>
            <div class="stat-label">Revenue Today</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value"><?= (int)$s6['CNT'] ?></div>
            <div class="stat-label">Kitchen Active</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value"><?= (int)$s4['CNT'] ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>

</div>