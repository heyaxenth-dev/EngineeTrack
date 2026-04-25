<?php
session_start();

// Check if the user is not authenticated
function checkLogin() {
    if (!isset($_SESSION['is_authenticated']) || $_SESSION['is_authenticated'] !== true) {
        // Set session variables for status message
        $_SESSION['status'] = "Denied Access!";
        $_SESSION['status_text'] = "Please Login to Access the Page";
        $_SESSION['status_code'] = "warning";
        $_SESSION['status_btn'] = "Back";
        
        // Redirect to login page
        header("Location: ../authentication/admin-login.php");
        exit; // Exit script to prevent further execution
    }
}

?>