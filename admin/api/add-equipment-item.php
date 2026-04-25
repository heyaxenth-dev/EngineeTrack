<?php
session_start();
include '../../database/conn.php';

if (!isset($_SESSION['is_authenticated']) || $_SESSION['is_authenticated'] !== true) {
    header('Location: ../../authentication/admin-login.php');
    exit();
}

$role = strtolower((string) ($_SESSION['user_role'] ?? ''));
if (!in_array($role, ['admin', 'administrator'], true)) {
    header('Location: ../../staff/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['addEquipment'])) {
    header('Location: ../equipment-inventory.php');
    exit();
}

function set_flash_and_redirect($status, $text, $code, $button = 'OK') {
    $_SESSION['status'] = $status;
    $_SESSION['status_text'] = $text;
    $_SESSION['status_code'] = $code;
    $_SESSION['status_btn'] = $button;
    header('Location: ../equipment-inventory.php');
    exit();
}

$name = trim($_POST['name'] ?? '');
$total_quantity = (int) ($_POST['total_quantity'] ?? 0);
$available_quantity = (int) ($_POST['available_quantity'] ?? -1);

if ($name === '' || $total_quantity < 1 || $available_quantity < 0) {
    set_flash_and_redirect('Error', 'Please provide valid equipment details.', 'error');
}

if ($available_quantity > $total_quantity) {
    set_flash_and_redirect('Error', 'Available quantity cannot be greater than total quantity.', 'error');
}

$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'equipment_items'");
if (!$table_check || mysqli_num_rows($table_check) === 0) {
    set_flash_and_redirect('Error', 'Equipment table is missing. Run migration first.', 'error');
}

$duplicate_stmt = $conn->prepare("SELECT id FROM equipment_items WHERE LOWER(name) = LOWER(?) LIMIT 1");
$duplicate_stmt->bind_param('s', $name);
$duplicate_stmt->execute();
$duplicate_result = $duplicate_stmt->get_result();
$is_duplicate = $duplicate_result && $duplicate_result->num_rows > 0;
$duplicate_stmt->close();

if ($is_duplicate) {
    set_flash_and_redirect('Error', 'Equipment item already exists.', 'error');
}

$insert_stmt = $conn->prepare("INSERT INTO equipment_items (name, total_quantity, available_quantity, is_active) VALUES (?, ?, ?, 1)");
$insert_stmt->bind_param('sii', $name, $total_quantity, $available_quantity);

if ($insert_stmt->execute()) {
    $insert_stmt->close();
    set_flash_and_redirect('Success', 'Equipment item added successfully.', 'success');
}

$insert_stmt->close();
set_flash_and_redirect('Error', 'Failed to add equipment item. Please try again.', 'error');

?>
