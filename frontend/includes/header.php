<?php
// header.php — included at top of every authenticated page.
// $pageTitle must be set before including this file.

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Restaurant DataBase/');
}

require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';

requireLogin();   // redirect to login if not authenticated

$flash = getFlash();

// Helper: returns 'active' if $page matches the current script filename.

function active(string $page): string {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Dashboard') ?> &mdash; Zam Zam Restaurant</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>frontend/assets/css/style.css">
</head>
<body>
<div class="app-wrapper">

<!-- SIDEBAR -->
<aside class="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <h1>Zam Zam</h1>
        <p>Restaurant Management System</p>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- MAIN – everyone -->
        <div class="nav-section">Main</div>
        <a href="<?= BASE_URL ?>frontend/pages/dashboard.php"
           class="<?= active('dashboard.php') ?>">
            Dashboard
        </a>

        <?php if (canManageOrders()): ?>
        <!-- RESTAURANT -->
        <div class="nav-section">Restaurant</div>
        <a href="<?= BASE_URL ?>frontend/pages/orders.php"
           class="<?= active('orders.php') ?>">
            Orders
        </a>
        <?php endif; ?>

        <?php if (canAccessKitchen()): ?>
        <a href="<?= BASE_URL ?>frontend/pages/kitchen.php"
           class="<?= active('kitchen.php') ?>">
            Kitchen
        </a>
        <?php endif; ?>

        <?php if (hasRole(['admin','manager','cashier','staff'])): ?>
        <a href="<?= BASE_URL ?>frontend/pages/payments.php"
           class="<?= active('payments.php') ?>">
            Payments
        </a>
        <?php endif; ?>

        <?php if (canManageMenu()): ?>
        <!-- MENU -->
        <div class="nav-section">Menu</div>
        <a href="<?= BASE_URL ?>frontend/pages/menu_items.php"
           class="<?= active('menu_items.php') ?>">
            Menu Items
        </a>
        <a href="<?= BASE_URL ?>frontend/pages/categories.php"
           class="<?= active('categories.php') ?>">
            Categories
        </a>
        <?php endif; ?>

        <?php if (canManageCustomers()): ?>
        <!-- PEOPLE -->
        <div class="nav-section">People</div>
        <a href="<?= BASE_URL ?>frontend/pages/customers.php"
           class="<?= active('customers.php') ?>">
            Customers
        </a>
        <?php endif; ?>

        <?php if (isAdminOrManager()): ?>
        <a href="<?= BASE_URL ?>frontend/pages/employees.php"
           class="<?= active('employees.php') ?>">
            Employees
        </a>
        <?php endif; ?>

        <?php if (canManageStock()): ?>
        <!-- STOCK -->
        <div class="nav-section">Stock</div>
        <a href="<?= BASE_URL ?>frontend/pages/inventory.php"
           class="<?= active('inventory.php') ?>">
            Inventory
        </a>
        <a href="<?= BASE_URL ?>frontend/pages/suppliers.php"
           class="<?= active('suppliers.php') ?>">
            Suppliers
        </a>
        <?php endif; ?>

        <?php if (isAdminOrManager()): ?>
        <!-- ADMIN PANEL -->
        <div class="nav-section">Admin Panel</div>
        <a href="<?= BASE_URL ?>frontend/pages/app_users.php"
           class="<?= active('app_users.php') ?>">
            App Users
        </a>
        <?php endif; ?>

    </nav>
</aside><!-- /.sidebar -->


<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="topbar">
        <h1><?= h($pageTitle ?? 'Dashboard') ?></h1>

        <div class="topbar-actions">
            <span style="color:var(--muted);font-size:12px;">
                <?= date('D, d M Y') ?>
            </span>

            <!-- User dropdown -->
            <div class="topbar-dropdown" id="userDropdown">

                <button class="topbar-dropdown-trigger"
                        onclick="toggleDropdown()">

                    <div class="user-avatar"
                         style="width:32px;height:32px;font-size:13px;flex-shrink:0;">
                        <?= strtoupper(substr(currentUser(), 0, 1)) ?>
                    </div>

                    <div style="line-height:1.2;text-align:left;">
                        <div style="font-size:12px;font-weight:600;color:var(--dark);">
                            <?= h(currentFullName()) ?>
                        </div>

                        <div style="font-size:11px;color:var(--muted);">
                            <?= h(currentRole()) ?>
                        </div>
                    </div>

                    <span style="font-size:10px;color:var(--muted);margin-left:2px;">
                        ▼
                    </span>
                </button>

                <div class="topbar-dropdown-menu" id="dropdownMenu">

                    <div class="dropdown-header">
                        <div style="font-weight:700;font-size:13px;color:var(--dark);">
                            <?= h(currentFullName()) ?>
                        </div>

                        <div style="font-size:11px;color:var(--muted);">
                            <?= h(currentRole()) ?>
                        </div>
                    </div>

                    <?php if (isAdminOrManager()): ?>
                    <a href="<?= BASE_URL ?>frontend/pages/app_users.php"
                       class="dropdown-item">
                        Admin Panel
                    </a>

                    <div class="dropdown-divider"></div>
                    <?php endif; ?>

                    <form method="POST"
                          action="<?= BASE_URL ?>frontend/pages/logout.php">

                        <button type="submit"
                                class="dropdown-item dropdown-item-danger">
                            Logout
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </div>
