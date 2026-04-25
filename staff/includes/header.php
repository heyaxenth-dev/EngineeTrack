<?php 
// Include the database connection file
include '../database/conn.php';
 // Get the current script name
    $current_page = basename($_SERVER['PHP_SELF'], ".php");

    $renamed_pages = [
    // 'Dashboard' => 'home',
    'dashboard' => 'Dashboard',
    'schedule-reservation' => 'Schedule and Reservation',
    'utilization' => 'Utilization',
    'equipment-inventory' => 'Equipment and Inventory',
    'reports' => 'Reports',
    'user-management' => 'User Management',
    'users-profile' => 'User Profile',
    // 'users-profile' => $user_firstname . " " . $user_lastname . "'s Profile",
    ];

    // Log in credentials: admin uses admin table, staff uses users table
    if (!empty($_SESSION['is_authenticated']) && $_SESSION['is_authenticated'] === true && $_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'administrator') 
    {
        $username = $_SESSION['username'] ?? '';
        $email = $_SESSION['email'] ?? '';
        $fname = $_SESSION['firstname'] ?? '';
        $lname = $_SESSION['lastname'] ?? '';
        $fullname = trim($fname . ' ' . $lname) ?: $username;
        $shortend_name = $fname ? (substr($fname, 0, 1) . '. ' . $lname) : $username;
        $role = 'Admin';
    } else {
        $username = $_SESSION['username'] ?? '';
        $email = $_SESSION['email'] ?? '';
        $fname = $_SESSION['firstname'] ?? '';
        $lname = $_SESSION['lastname'] ?? '';
        $fullname = trim($fname . ' ' . $lname) ?: $username;
        $shortend_name = $fname ? (substr($fname, 0, 1) . '. ' . $lname) : $username;
        $role = 'Staff';
    }
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title><?= $renamed_pages[$current_page] ?> - EngineeTrack</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon" />
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet" />

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet" />
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet" />
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet" />
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet" />
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet" />

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>
    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="assets/img/logo.png" alt="" />
                <span class="d-none d-lg-block">EngineeTrack</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        <!-- End Logo -->


        <!-- End Icons Navigation -->
    </header>
    <!-- End Header -->