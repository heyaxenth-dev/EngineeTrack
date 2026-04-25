<?php
session_start();

// Admin pages require authenticated admin/administrator accounts.
function checkAdminLogin() {
    if (!isset($_SESSION['is_authenticated']) || $_SESSION['is_authenticated'] !== true) {
        $_SESSION['status'] = "Denied Access!";
        $_SESSION['status_text'] = "Please Login to Access the Page";
        $_SESSION['status_code'] = "warning";
        $_SESSION['status_btn'] = "Back";
        header("Location: ../authentication/admin-login.php");
        exit;
    }

    $role = strtolower((string) ($_SESSION['user_role'] ?? ''));
    if (!in_array($role, ['admin', 'administrator'], true)) {
        header("Location: ../staff/dashboard.php");
        exit;
    }
}

// Auto-enforce auth when this file is included.
checkAdminLogin();
?>