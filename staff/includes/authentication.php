<?php
session_start();

// Staff pages require authenticated non-admin accounts.
function checkStaffLogin() {
    if (!isset($_SESSION['is_authenticated']) || $_SESSION['is_authenticated'] !== true) {
        $_SESSION['status'] = "Denied Access!";
        $_SESSION['status_text'] = "Please Login to Access the Page";
        $_SESSION['status_code'] = "warning";
        $_SESSION['status_btn'] = "Back";
        header("Location: ../authentication/staff-login.php");
        exit;
    }

    $role = strtolower((string) ($_SESSION['user_role'] ?? ''));
    if (in_array($role, ['admin', 'administrator'], true)) {
        header("Location: ../admin/dashboard.php");
        exit;
    }
}

// Auto-enforce auth when this file is included.
checkStaffLogin();
?>