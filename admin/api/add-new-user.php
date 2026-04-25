<?php 
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
// include database connection
include '../../database/conn.php';

// echo 'testing the page';
if(isset($_POST['addUser'])) {
    // Retrieve form data
    $name = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $department = $_POST['department'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $account_status = isset($_POST['account_status']) ? 1 : 0;
    $permission_view = isset($_POST['permission_view']) ? 1 : 0;
    $permission_edit = isset($_POST['permission_edit']) ? 1 : 0;
    $permission_delete = isset($_POST['permission_delete']) ? 1 : 0;
    $permission_manage_settings = isset($_POST['permission_manage_settings']) ? 1 : 0;

    // Validate form data (you can add more validation as needed)
    if ($password !== $confirmPassword) {
        $_SESSION['status'] = "Error";
        $_SESSION['status_text'] = "Passwords do not match.";
        $_SESSION['status_code'] = "error";
        $_SESSION['status_btn'] = "OK";
        header("Location: ../user-management.php");
        exit();
    }

    // Hash the password before storing it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL statement to insert the new user
    $stmt = $conn->prepare("INSERT INTO users (name, username, email, contact, department, role, password, account_status, permission_view, permission_edit, permission_delete, permission_manage_settings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssiiiii", $name, $username, $email, $contact, $department, $role, $hashedPassword, $account_status, $permission_view, $permission_edit, $permission_delete, $permission_manage_settings);

    if ($stmt->execute()) {
        $_SESSION['status'] = "Success";
        $_SESSION['status_text'] = "New user added successfully.";
        $_SESSION['status_code'] = "success";
        $_SESSION['status_btn'] = "OK";
    } else {
        $_SESSION['status'] = "Error";
        $_SESSION['status_text'] = "Failed to add new user. Please try again.";
        $_SESSION['status_code'] = "error";
        $_SESSION['status_btn'] = "OK";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to the user management page
    header("Location: ../user-management.php");
    exit();

    
}


?>