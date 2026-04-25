<?php
include 'includes/authentication.php';
include '../database/conn.php';

$equipment_rows = [];
$schema_ready = true;

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['equipment_items', 'reservation_requests', 'reservation_request_items'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        $schema_ready = false;
        break;
    }
}

if ($schema_ready) {
    $equipment_query = "
        SELECT
            ei.id,
            ei.name,
            ei.total_quantity,
            ei.available_quantity,
            (ei.total_quantity - ei.available_quantity) AS borrowed_quantity,
            COALESCE((
                SELECT GROUP_CONCAT(DISTINCT rr.department SEPARATOR ', ')
                FROM reservation_request_items rri2
                INNER JOIN reservation_requests rr ON rr.id = rri2.reservation_request_id
                WHERE rri2.item_type = 'equipment'
                  AND rri2.resource_id = ei.id
                  AND rr.status = 'approved'
                  AND rr.end_datetime >= NOW()
            ), 'N/A') AS borrowing_departments,
            (
                SELECT MIN(rr.end_datetime)
                FROM reservation_request_items rri3
                INNER JOIN reservation_requests rr ON rr.id = rri3.reservation_request_id
                WHERE rri3.item_type = 'equipment'
                  AND rri3.resource_id = ei.id
                  AND rr.status = 'approved'
                  AND rr.end_datetime >= NOW()
            ) AS expected_return_datetime
        FROM equipment_items ei
        WHERE ei.is_active = 1
        ORDER BY ei.name ASC
    ";
    $equipment_result = mysqli_query($conn, $equipment_query);
    if ($equipment_result) {
        while ($row = mysqli_fetch_assoc($equipment_result)) {
            $equipment_rows[] = $row;
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'alert.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1><?= $renamed_pages[$current_page] ?></h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active"><?= $renamed_pages[$current_page] ?></li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <?php if (!$schema_ready): ?>
            <div class="alert alert-warning">
                Equipment tables are not ready yet. Run `database/migrations/2026_04_25_create_staff_reservation_tables.sql`
                and refresh this page.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="card-title mb-0">Equipment List</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addEquipmentModal">
                                <i class="bi bi-plus-circle me-1"></i>Add Equipment
                            </button>
                        </div>
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Total Quantity</th>
                                    <th>Total Quantity Borrowed</th>
                                    <th>Available Quantity</th>
                                    <th>Borrowing College/Office</th>
                                    <th>Expected Return Date</th>
                                    <th>Returned Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($equipment_rows)): ?>
                                    <?php foreach ($equipment_rows as $row): ?>
                                        <tr>
                                            <td><?= h($row['name']) ?></td>
                                            <td><?= (int) $row['total_quantity'] ?></td>
                                            <td><?= max(0, (int) $row['borrowed_quantity']) ?></td>
                                            <td><?= (int) $row['available_quantity'] ?></td>
                                            <td><?= h($row['borrowing_departments']) ?></td>
                                            <td>
                                                <?= !empty($row['expected_return_datetime']) ? date('M d, Y h:i A', strtotime($row['expected_return_datetime'])) : 'N/A' ?>
                                            </td>
                                            <td>0</td>
                                            <td>
                                                <a href="utilization.php" class="btn btn-outline-primary btn-sm">
                                                    View Requests
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No equipment data found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEquipmentModalLabel">Add Equipment Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="api/add-equipment-item.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Equipment Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity</label>
                            <input type="number" class="form-control" name="total_quantity" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available Quantity</label>
                            <input type="number" class="form-control" name="available_quantity" min="0" required>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        Tip: Available quantity must not exceed total quantity.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="addEquipment">Save Item</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>