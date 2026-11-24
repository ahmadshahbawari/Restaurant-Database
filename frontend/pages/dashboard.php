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
<!-- Quick links -->
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">

    <a href="<?= BASE_URL ?>frontend/pages/orders.php?action=new"
       class="btn btn-primary">
        New Order
    </a>

    <a href="<?= BASE_URL ?>frontend/pages/kitchen.php"
       class="btn btn-warning">
        Kitchen Board
    </a>

    <a href="<?= BASE_URL ?>frontend/pages/payments.php"
       class="btn btn-success">
        Payments
    </a>

    <a href="<?= BASE_URL ?>frontend/pages/inventory.php"
       class="btn btn-info">
        Inventory
    </a>

</div>

<!-- Recent Orders -->
<div class="card">

    <div class="card-header">
        <h3>Recent Orders</h3>

        <a href="<?= BASE_URL ?>frontend/pages/orders.php"
           class="btn btn-outline btn-sm">
            View All
        </a>
    </div>

    <div class="card-body" style="padding:0;">

        <?php if (empty($recentOrders)): ?>

            <div class="empty-state">
                <p>No orders yet today.</p>
            </div>

        <?php else: ?>

            <div class="table-wrapper">
                <table>

                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($recentOrders as $o): ?>

                        <tr>
                            <td>
                                <strong>#<?= h($o['ORDER_ID']) ?></strong>
                            </td>

                            <td><?= h($o['CUSTOMER_NAME']) ?></td>

                            <td><?= h($o['ORDER_TYPE']) ?></td>

                            <td><?= h($o['ORDER_DATE']) ?></td>

                            <td><?= money((float)$o['TOTAL_AMOUNT']) ?></td>

                            <td><?= statusBadge($o['STATUS']) ?></td>

                            <td>
                                <a href="<?= BASE_URL ?>frontend/pages/orders.php?action=view&id=<?= (int)$o['ORDER_ID'] ?>"
                                   class="btn btn-info btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>
</div>