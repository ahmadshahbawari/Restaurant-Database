<?php

 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function currentRole(): string {
    return isset($_SESSION['role']) ? (string)$_SESSION['role'] : '';
}

 
function currentRoleLower(): string {
    return strtolower(trim(currentRole()));
}

 
function hasRole(array $allowed): bool {
    $role = currentRoleLower();
    foreach ($allowed as $a) {
        if (strtolower(trim($a)) === $role) {
            return true;
        }
    }
    return false;
}

 
function requireRole(array $allowed): void {
    requireLogin();
    if (!hasRole($allowed)) {
        // Store a flash message before redirecting
        $_SESSION['flash_type'] = 'danger';
        $_SESSION['flash_msg']  = 'Access denied — you do not have permission to view that page.';
        header('Location: ' . BASE_URL . 'frontend/pages/dashboard.php');
        exit;
    }
}

/**
 * Redirects to login if the user is not authenticated.
 */
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Returns the currently logged-in username.
 */
function currentUser(): string {
    return isset($_SESSION['username']) ? (string)$_SESSION['username'] : '';
}

/**
 * Returns the full name stored in session (falls back to username).
 */
function currentFullName(): string {
    return isset($_SESSION['full_name']) && $_SESSION['full_name'] !== ''
        ? (string)$_SESSION['full_name']
        : currentUser();
}


function isAdminOrManager(): bool {
    return hasRole(['admin', 'manager']);
}

/** True if the user can manage staff (Employees / App Users pages). */
function canManageStaff(): bool {
    return hasRole(['admin', 'manager']);
}

/** True if the user can view/edit Suppliers & Inventory. */
function canManageStock(): bool {
    return hasRole(['admin', 'manager', 'supplier']);
}

/** True if the user can view/manage Orders & Payments. */
function canManageOrders(): bool {
    return hasRole(['admin', 'manager', 'cashier', 'waiter', 'staff']);
}

/** True if the user can access the Kitchen board. */
function canAccessKitchen(): bool {
    return hasRole(['admin', 'manager', 'cashier', 'waiter', 'chef', 'staff']);
}

/** True if the user can manage Customers. */
function canManageCustomers(): bool {
    return hasRole(['admin', 'manager', 'cashier', 'waiter']);
}

/** True if the user can access Menu Items / Categories. */
function canManageMenu(): bool {
    return hasRole(['admin', 'manager', 'cashier', 'waiter', 'chef']);
}
