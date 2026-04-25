<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../database/conn.php';

if (isset($_POST['updateUser'])) {
    $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $name = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $department = $_POST['department'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $account_status = isset($_POST['account_status']) ? 1 : 0;
    $permission_view = isset($_POST['permission_view']) ? 1 : 0;
    $permission_edit = isset($_POST['permission_edit']) ? 1 : 0;
    $permission_delete = isset($_POST['permission_delete']) ? 1 : 0;
    $permission_manage_settings = isset($_POST['permission_manage_settings']) ? 1 : 0;

    if ($user_id <= 0) {
        $_SESSION['status'] = "Error";
        $_SESSION['status_text'] = "Invalid user selected.";
        $_SESSION['status_code'] = "error";
        $_SESSION['status_btn'] = "OK";
        header("Location: ../user-management.php");
        exit();
    }

    if (!empty($password) || !empty($confirmPassword)) {
        if ($password !== $confirmPassword) {
            $_SESSION['status'] = "Error";
            $_SESSION['status_text'] = "Passwords do not match.";
            $_SESSION['status_code'] = "error";
            $_SESSION['status_btn'] = "OK";
            header("Location: ../user-management.php");
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, contact = ?, department = ?, role = ?, account_status = ?, permission_view = ?, permission_edit = ?, permission_delete = ?, permission_manage_settings = ?, password = ? WHERE id = ?");
        $stmt->bind_param(
            "ssssssiiiiisi",
            $name,
            $username,
            $email,
            $contact,
            $department,
            $role,
            $account_status,
            $permission_view,
            $permission_edit,
            $permission_delete,
            $permission_manage_settings,
            $hashedPassword,
            $user_id
        );
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, contact = ?, department = ?, role = ?, account_status = ?, permission_view = ?, permission_edit = ?, permission_delete = ?, permission_manage_settings = ? WHERE id = ?");
        $stmt->bind_param(
            "ssssssiiiiii",
            $name,
            $username,
            $email,
            $contact,
            $department,
            $role,
            $account_status,
            $permission_view,
            $permission_edit,
            $permission_delete,
            $permission_manage_settings,
            $user_id
        );
    }

    if ($stmt->execute()) {
        $_SESSION['status'] = "Success";
        $_SESSION['status_text'] = "User details updated successfully.";
        $_SESSION['status_code'] = "success";
        $_SESSION['status_btn'] = "OK";
    } else {
        $_SESSION['status'] = "Error";
        $_SESSION['status_text'] = "Failed to update user details. Please try again.";
        $_SESSION['status_code'] = "error";
        $_SESSION['status_btn'] = "OK";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../user-management.php");
    exit();
}

?>
