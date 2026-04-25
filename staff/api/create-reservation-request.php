<?php
session_start();
include '../../database/conn.php';

if (!isset($_SESSION['is_authenticated']) || $_SESSION['is_authenticated'] !== true) {
    header('Location: ../../authentication/staff-login.php');
    exit();
}

$current_user_id = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $current_user_id <= 0) {
    header('Location: ../schedule-reservation.php');
    exit();
}

function set_flash_and_redirect($status, $text, $code, $button = 'OK') {
    $_SESSION['status'] = $status;
    $_SESSION['status_text'] = $text;
    $_SESSION['status_code'] = $code;
    $_SESSION['status_btn'] = $button;
    header('Location: ../schedule-reservation.php');
    exit();
}

$event_title = trim($_POST['event_title'] ?? '');
$department = trim($_POST['department'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');
$request_date = trim($_POST['request_date'] ?? '');
$start_time = trim($_POST['start_time'] ?? '');
$end_time = trim($_POST['end_time'] ?? '');
$venue_id = isset($_POST['venue_id']) && $_POST['venue_id'] !== '' ? (int) $_POST['venue_id'] : 0;
$equipment_id = isset($_POST['equipment_id']) && $_POST['equipment_id'] !== '' ? (int) $_POST['equipment_id'] : 0;
$equipment_quantity = max(1, (int) ($_POST['equipment_quantity'] ?? 1));
$notes = trim($_POST['notes'] ?? '');

if ($event_title === '' || $department === '' || $purpose === '' || $request_date === '' || $start_time === '' || $end_time === '') {
    set_flash_and_redirect('Error', 'Please complete all required fields.', 'error');
}

if ($venue_id <= 0 && $equipment_id <= 0) {
    set_flash_and_redirect('Error', 'Select at least one resource (venue or equipment).', 'error');
}

$start_datetime = $request_date . ' ' . $start_time . ':00';
$end_datetime = $request_date . ' ' . $end_time . ':00';
if (strtotime($start_datetime) === false || strtotime($end_datetime) === false || strtotime($start_datetime) >= strtotime($end_datetime)) {
    set_flash_and_redirect('Error', 'Invalid schedule range. End time must be later than start time.', 'error');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['venues', 'equipment_items', 'reservation_requests', 'reservation_request_items'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        set_flash_and_redirect('Error', 'Reservation tables are missing. Run migration first.', 'error');
    }
}

$resource_items = [];

if ($venue_id > 0) {
    $venue_stmt = $conn->prepare("SELECT id, name FROM venues WHERE id = ? AND is_active = 1 LIMIT 1");
    $venue_stmt->bind_param('i', $venue_id);
    $venue_stmt->execute();
    $venue_result = $venue_stmt->get_result();
    $venue = $venue_result ? $venue_result->fetch_assoc() : null;
    $venue_stmt->close();

    if (!$venue) {
        set_flash_and_redirect('Error', 'Selected venue is not available.', 'error');
    }

    $conflict_stmt = $conn->prepare("
        SELECT rr.id
        FROM reservation_requests rr
        INNER JOIN reservation_request_items rri ON rr.id = rri.reservation_request_id
        WHERE rri.item_type = 'venue'
          AND rri.resource_id = ?
          AND rr.status IN ('pending', 'approved')
          AND NOT (rr.end_datetime <= ? OR rr.start_datetime >= ?)
        LIMIT 1
    ");
    $conflict_stmt->bind_param('iss', $venue_id, $start_datetime, $end_datetime);
    $conflict_stmt->execute();
    $conflict_result = $conflict_stmt->get_result();
    $has_conflict = $conflict_result && $conflict_result->num_rows > 0;
    $conflict_stmt->close();

    if ($has_conflict) {
        set_flash_and_redirect('Error', 'The selected venue is already requested within that time range.', 'error');
    }

    $resource_items[] = [
        'item_type' => 'venue',
        'resource_id' => (int) $venue['id'],
        'resource_name' => $venue['name'],
        'quantity' => 1,
    ];
}

if ($equipment_id > 0) {
    $equipment_stmt = $conn->prepare("SELECT id, name, available_quantity FROM equipment_items WHERE id = ? AND is_active = 1 LIMIT 1");
    $equipment_stmt->bind_param('i', $equipment_id);
    $equipment_stmt->execute();
    $equipment_result = $equipment_stmt->get_result();
    $equipment = $equipment_result ? $equipment_result->fetch_assoc() : null;
    $equipment_stmt->close();

    if (!$equipment) {
        set_flash_and_redirect('Error', 'Selected equipment is not available.', 'error');
    }

    if ($equipment_quantity > (int) $equipment['available_quantity']) {
        set_flash_and_redirect('Error', 'Requested equipment quantity exceeds available stock.', 'error');
    }

    $resource_items[] = [
        'item_type' => 'equipment',
        'resource_id' => (int) $equipment['id'],
        'resource_name' => $equipment['name'],
        'quantity' => $equipment_quantity,
    ];
}

$request_code = 'REQ-' . date('YmdHis') . '-' . random_int(100, 999);
$status = 'pending';

$conn->begin_transaction();

try {
    $request_stmt = $conn->prepare("
        INSERT INTO reservation_requests
        (request_code, requested_by_user_id, event_title, department, purpose, request_date, start_datetime, end_datetime, status, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $request_stmt->bind_param(
        'sissssssss',
        $request_code,
        $current_user_id,
        $event_title,
        $department,
        $purpose,
        $request_date,
        $start_datetime,
        $end_datetime,
        $status,
        $notes
    );
    $request_stmt->execute();
    $reservation_request_id = (int) $request_stmt->insert_id;
    $request_stmt->close();

    $item_stmt = $conn->prepare("
        INSERT INTO reservation_request_items
        (reservation_request_id, item_type, resource_id, resource_name, quantity)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($resource_items as $item) {
        $item_stmt->bind_param(
            'isisi',
            $reservation_request_id,
            $item['item_type'],
            $item['resource_id'],
            $item['resource_name'],
            $item['quantity']
        );
        $item_stmt->execute();
    }
    $item_stmt->close();

    $conn->commit();
    set_flash_and_redirect('Success', 'Reservation request created successfully.', 'success');
} catch (Throwable $exception) {
    $conn->rollback();
    set_flash_and_redirect('Error', 'Failed to create reservation request. Please try again.', 'error');
}

?>
