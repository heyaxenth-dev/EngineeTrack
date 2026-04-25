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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../schedule-reservation.php');
    exit();
}

function set_flash_and_back($status, $text, $code, $button = 'OK') {
    $_SESSION['status'] = $status;
    $_SESSION['status_text'] = $text;
    $_SESSION['status_code'] = $code;
    $_SESSION['status_btn'] = $button;
    header('Location: ../schedule-reservation.php');
    exit();
}

$request_id = (int) ($_POST['request_id'] ?? 0);
$action = strtolower(trim($_POST['action'] ?? ''));
$target_status = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : '');

if ($request_id <= 0 || $target_status === '') {
    set_flash_and_back('Error', 'Invalid request action.', 'error');
}

$request_stmt = $conn->prepare("SELECT id, status, start_datetime, end_datetime FROM reservation_requests WHERE id = ? LIMIT 1");
$request_stmt->bind_param('i', $request_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
$request_row = $request_result ? $request_result->fetch_assoc() : null;
$request_stmt->close();

if (!$request_row) {
    set_flash_and_back('Error', 'Reservation request not found.', 'error');
}

$current_status = strtolower((string) $request_row['status']);
if ($current_status === $target_status) {
    set_flash_and_back('Info', 'Request is already marked as ' . $target_status . '.', 'info');
}

$start_datetime = $request_row['start_datetime'];
$end_datetime = $request_row['end_datetime'];

$items_stmt = $conn->prepare("SELECT id, item_type, resource_id, quantity FROM reservation_request_items WHERE reservation_request_id = ?");
$items_stmt->bind_param('i', $request_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$items_stmt->close();

$equipment_totals = [];
foreach ($items as $item) {
    if ($item['item_type'] === 'equipment') {
        $resource_id = (int) $item['resource_id'];
        $equipment_totals[$resource_id] = ($equipment_totals[$resource_id] ?? 0) + (int) $item['quantity'];
    }
}

$conn->begin_transaction();

try {
    if ($target_status === 'approved') {
        foreach ($items as $item) {
            if ($item['item_type'] === 'venue') {
                $venue_id = (int) $item['resource_id'];
                $conflict_stmt = $conn->prepare("
                    SELECT rr.id
                    FROM reservation_requests rr
                    INNER JOIN reservation_request_items rri ON rr.id = rri.reservation_request_id
                    WHERE rr.id <> ?
                      AND rr.status = 'approved'
                      AND rri.item_type = 'venue'
                      AND rri.resource_id = ?
                      AND NOT (rr.end_datetime <= ? OR rr.start_datetime >= ?)
                    LIMIT 1
                ");
                $conflict_stmt->bind_param('iiss', $request_id, $venue_id, $start_datetime, $end_datetime);
                $conflict_stmt->execute();
                $conflict_result = $conflict_stmt->get_result();
                $has_conflict = $conflict_result && $conflict_result->num_rows > 0;
                $conflict_stmt->close();

                if ($has_conflict) {
                    throw new Exception('Cannot approve: selected venue overlaps with another approved request.');
                }
            }
        }

        if ($current_status !== 'approved') {
            foreach ($equipment_totals as $equipment_id => $quantity_needed) {
                $stock_stmt = $conn->prepare("SELECT available_quantity FROM equipment_items WHERE id = ? FOR UPDATE");
                $stock_stmt->bind_param('i', $equipment_id);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();
                $stock_row = $stock_result ? $stock_result->fetch_assoc() : null;
                $stock_stmt->close();

                if (!$stock_row || (int) $stock_row['available_quantity'] < $quantity_needed) {
                    throw new Exception('Cannot approve: insufficient equipment stock for one or more items.');
                }

                $update_stock_stmt = $conn->prepare("UPDATE equipment_items SET available_quantity = available_quantity - ? WHERE id = ?");
                $update_stock_stmt->bind_param('ii', $quantity_needed, $equipment_id);
                $update_stock_stmt->execute();
                $update_stock_stmt->close();
            }
        }
    } elseif ($target_status === 'rejected' && $current_status === 'approved') {
        foreach ($equipment_totals as $equipment_id => $quantity_to_restore) {
            $restore_stmt = $conn->prepare("UPDATE equipment_items SET available_quantity = available_quantity + ? WHERE id = ?");
            $restore_stmt->bind_param('ii', $quantity_to_restore, $equipment_id);
            $restore_stmt->execute();
            $restore_stmt->close();
        }
    }

    $update_request_stmt = $conn->prepare("UPDATE reservation_requests SET status = ? WHERE id = ?");
    $update_request_stmt->bind_param('si', $target_status, $request_id);
    $update_request_stmt->execute();
    $update_request_stmt->close();

    $conn->commit();
    set_flash_and_back('Success', 'Request updated successfully.', 'success');
} catch (Throwable $exception) {
    $conn->rollback();
    set_flash_and_back('Error', $exception->getMessage(), 'error');
}

?>
