<?php
include 'includes/authentication.php';
include 'includes/header.php';
include 'includes/sidebar.php';
include 'alert.php';

$users = [];
$total_users = 0;
$admin_accounts = 0;

$users_query = "SELECT id, name, username, email, contact, department, role, account_status, permission_view, permission_edit, permission_delete, permission_manage_settings FROM users ORDER BY id DESC";
$users_result = mysqli_query($conn, $users_query);

if ($users_result) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users[] = $row;
    }
    $total_users = count($users);
}

$admin_count_query = "SELECT COUNT(*) AS admin_total FROM users WHERE LOWER(role) IN ('admin', 'administrator')";
$admin_count_result = mysqli_query($conn, $admin_count_query);
if ($admin_count_result && $admin_row = mysqli_fetch_assoc($admin_count_result)) {
    $admin_accounts = (int) $admin_row['admin_total'];
}

$active_users = 0;
$inactive_users = 0;
foreach ($users as $user_count_row) {
    if ((int) $user_count_row['account_status'] === 1) {
        $active_users++;
    } else {
        $inactive_users++;
    }
}
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
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="card-title mb-0">User Management</h5>
                                <small class="text-muted">Manage user accounts, roles, and access permissions.</small>
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus me-1"></i>Add New User
                            </button>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Total Users</small>
                                    <h4 class="mb-0"><?= $total_users ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Active Users</small>
                                    <h4 class="mb-0 text-success"><?= $active_users ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Inactive Users</small>
                                    <h4 class="mb-0 text-secondary"><?= $inactive_users ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Admin Accounts</small>
                                    <h4 class="mb-0 text-primary"><?= $admin_accounts ?></h4>
                                </div>
                            </div>
                        </div>

                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th>Email Address</th>
                                    <th>Department / Office</th>
                                    <th>User Role</th>
                                    <th>Account Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['department']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                                    <td>
                                        <?php if ((int) $user['account_status'] === 1): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#viewUserModal"
                                                data-id="<?= (int) $user['id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-contact="<?= htmlspecialchars($user['contact'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-department="<?= htmlspecialchars($user['department'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-role="<?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8') ?>"
                                                data-account-status="<?= (int) $user['account_status'] ?>"
                                                data-permission-view="<?= (int) $user['permission_view'] ?>"
                                                data-permission-edit="<?= (int) $user['permission_edit'] ?>"
                                                data-permission-delete="<?= (int) $user['permission_delete'] ?>"
                                                data-permission-manage="<?= (int) $user['permission_manage_settings'] ?>">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="<?= (int) $user['id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-contact="<?= htmlspecialchars($user['contact'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-department="<?= htmlspecialchars($user['department'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-account-status="<?= (int) $user['account_status'] ?>"
                                                data-permission-view="<?= (int) $user['permission_view'] ?>"
                                                data-permission-edit="<?= (int) $user['permission_edit'] ?>"
                                                data-permission-delete="<?= (int) $user['permission_delete'] ?>"
                                                data-permission-manage="<?= (int) $user['permission_manage_settings'] ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No users found in the database.</td>
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
<!-- End #main -->

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" action="api/add-new-user.php" method="POST">
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">User Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required />
                                <div class="invalid-feedback">Full name is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required />
                                <div class="invalid-feedback">Username is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required />
                                <div class="invalid-feedback">Enter a valid email address.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact" name="contact" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department of Office</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="" selected disabled>Select Department</option>
                                    <option>Engineering Department</option>
                                    <option>Operations Office</option>
                                    <option>Maintenance Unit</option>
                                </select>
                                <div class="invalid-feedback">Please select a department.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="" selected disabled>Select Role</option>
                                    <option>Administrator</option>
                                    <option>Supervisor</option>
                                    <option>Staff</option>
                                </select>
                                <div class="invalid-feedback">Please select a role.</div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">Account Settings</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" value=""
                                    required />
                                <div class="invalid-feedback">Password must be at least 8 characters.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    value="" required />
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showAddUserPasswords" />
                                    <label class="form-check-label" for="showAddUserPasswords">
                                        Show Password and Confirm Password
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoGeneratePassword" />
                                    <label class="form-check-label" for="autoGeneratePassword">
                                        Auto-generate Password
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                                <label class="form-label mb-0 me-1">Account Status:</label>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="accountStatusSwitch"
                                        name="account_status" value="1" checked />
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span>Active</span>
                                    <span>Inactive</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <h6 class="mb-3">Permissions</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permissionView"
                                        name="permission_view" value="1" checked />
                                    <label class="form-check-label" for="permissionView">View Access</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permissionEdit"
                                        name="permission_edit" value="1" checked />
                                    <label class="form-check-label" for="permissionEdit">Edit Access</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permissionDelete"
                                        name="permission_delete" value="1" />
                                    <label class="form-check-label" for="permissionDelete">Delete Access</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permissionManage"
                                        name="permission_manage_settings" value="1" />
                                    <label class="form-check-label" for="permissionManage">Manage Settings</label>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="addUserSubmit" name="addUser">Save User</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="reset" class="btn btn-outline-secondary">Reset Form</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <small class="text-muted d-block">User ID</small>
                        <strong id="view_user_id">-</strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Full Name</small>
                        <strong id="view_user_name">-</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Username</small>
                        <strong id="view_user_username">-</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Role</small>
                        <strong id="view_user_role">-</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Account Status</small>
                        <strong id="view_user_account_status">-</strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Email Address</small>
                        <strong id="view_user_email">-</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Contact Number</small>
                        <strong id="view_user_contact">-</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Department / Office</small>
                        <strong id="view_user_department">-</strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Permissions</small>
                        <div class="d-flex flex-wrap gap-2">
                            <span id="view_permission_view" class="badge bg-secondary">View: No</span>
                            <span id="view_permission_edit" class="badge bg-secondary">Edit: No</span>
                            <span id="view_permission_delete" class="badge bg-secondary">Delete: No</span>
                            <span id="view_permission_manage" class="badge bg-secondary">Manage: No</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" action="api/update-user.php" method="POST">
                    <input type="hidden" id="edit_user_id" name="user_id">

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">User Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="edit_fullname" name="fullname" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="edit_contact" name="contact" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department of Office</label>
                                <select class="form-select" id="edit_department" name="department" required>
                                    <option value="Engineering Department">Engineering Department</option>
                                    <option value="Operations Office">Operations Office</option>
                                    <option value="Maintenance Unit">Maintenance Unit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Role</label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">Reset Password (Optional)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" id="edit_password" name="password" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="edit_confirm_password"
                                    name="confirmPassword" />
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">Account Status</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_account_status"
                                name="account_status" value="1" />
                            <label class="form-check-label" for="edit_account_status">Active account</label>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <h6 class="mb-3">Permissions</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_permission_view"
                                        name="permission_view" value="1" />
                                    <label class="form-check-label" for="edit_permission_view">View Access</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_permission_edit"
                                        name="permission_edit" value="1" />
                                    <label class="form-check-label" for="edit_permission_edit">Edit Access</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_permission_delete"
                                        name="permission_delete" value="1" />
                                    <label class="form-check-label" for="edit_permission_delete">Delete Access</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_permission_manage"
                                        name="permission_manage_settings" value="1" />
                                    <label class="form-check-label" for="edit_permission_manage">Manage Settings</label>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning" name="updateUser">Update User</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var viewUserModal = document.getElementById('viewUserModal');
    var editUserModal = document.getElementById('editUserModal');

    if (viewUserModal) {
        viewUserModal.addEventListener('show.bs.modal', function(event) {
            var triggerButton = event.relatedTarget;
            if (!triggerButton) {
                return;
            }

            document.getElementById('view_user_id').textContent = triggerButton.getAttribute(
                'data-id') || '-';
            document.getElementById('view_user_name').textContent = triggerButton.getAttribute(
                'data-name') || '-';
            document.getElementById('view_user_username').textContent = triggerButton.getAttribute(
                'data-username') || '-';
            document.getElementById('view_user_email').textContent = triggerButton.getAttribute(
                'data-email') || '-';
            document.getElementById('view_user_contact').textContent = triggerButton.getAttribute(
                'data-contact') || '-';
            document.getElementById('view_user_department').textContent = triggerButton.getAttribute(
                'data-department') || '-';
            document.getElementById('view_user_role').textContent = triggerButton.getAttribute(
                'data-role') || '-';
            document.getElementById('view_user_account_status').textContent = triggerButton
                .getAttribute('data-account-status') === '1' ? 'Active' : 'Inactive';

            var permissionView = triggerButton.getAttribute('data-permission-view') === '1';
            var permissionEdit = triggerButton.getAttribute('data-permission-edit') === '1';
            var permissionDelete = triggerButton.getAttribute('data-permission-delete') === '1';
            var permissionManage = triggerButton.getAttribute('data-permission-manage') === '1';

            var viewBadge = document.getElementById('view_permission_view');
            var editBadge = document.getElementById('view_permission_edit');
            var deleteBadge = document.getElementById('view_permission_delete');
            var manageBadge = document.getElementById('view_permission_manage');

            viewBadge.className = 'badge ' + (permissionView ? 'bg-success' : 'bg-secondary');
            editBadge.className = 'badge ' + (permissionEdit ? 'bg-success' : 'bg-secondary');
            deleteBadge.className = 'badge ' + (permissionDelete ? 'bg-success' : 'bg-secondary');
            manageBadge.className = 'badge ' + (permissionManage ? 'bg-success' : 'bg-secondary');

            viewBadge.textContent = 'View: ' + (permissionView ? 'Yes' : 'No');
            editBadge.textContent = 'Edit: ' + (permissionEdit ? 'Yes' : 'No');
            deleteBadge.textContent = 'Delete: ' + (permissionDelete ? 'Yes' : 'No');
            manageBadge.textContent = 'Manage: ' + (permissionManage ? 'Yes' : 'No');
        });
    }

    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            var triggerButton = event.relatedTarget;
            if (!triggerButton) {
                return;
            }

            document.getElementById('edit_user_id').value = triggerButton.getAttribute('data-id') || '';
            document.getElementById('edit_fullname').value = triggerButton.getAttribute('data-name') ||
                '';
            document.getElementById('edit_username').value = triggerButton.getAttribute(
                'data-username') || '';
            document.getElementById('edit_email').value = triggerButton.getAttribute('data-email') ||
                '';
            document.getElementById('edit_contact').value = triggerButton.getAttribute(
                'data-contact') || '';
            document.getElementById('edit_department').value = triggerButton.getAttribute(
                'data-department') || '';
            document.getElementById('edit_role').value = triggerButton.getAttribute('data-role') || '';
            document.getElementById('edit_account_status').checked = triggerButton.getAttribute(
                'data-account-status') === '1';

            document.getElementById('edit_password').value = '';
            document.getElementById('edit_confirm_password').value = '';

            document.getElementById('edit_permission_view').checked = triggerButton.getAttribute(
                'data-permission-view') === '1';
            document.getElementById('edit_permission_edit').checked = triggerButton.getAttribute(
                'data-permission-edit') === '1';
            document.getElementById('edit_permission_delete').checked = triggerButton.getAttribute(
                'data-permission-delete') === '1';
            document.getElementById('edit_permission_manage').checked = triggerButton.getAttribute(
                'data-permission-manage') === '1';
        });
    }
});

$(function() {
    var $form = $('#addUserForm');
    var $submitButton = $('#addUserSubmit');
    var $requiredFields = $('#fullname, #username, #email, #department, #role, #password, #confirmPassword');

    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function setFieldState($field, isValid) {
        if (isValid) {
            $field.removeClass('is-invalid');
        } else {
            $field.addClass('is-invalid');
        }
    }

    function validateAddUserForm() {
        var fullname = $.trim($('#fullname').val());
        var username = $.trim($('#username').val());
        var email = $.trim($('#email').val());
        var department = $('#department').val();
        var role = $('#role').val();
        var password = $('#password').val();
        var confirmPassword = $('#confirmPassword').val();

        var fullnameValid = fullname.length > 0;
        var usernameValid = username.length > 0;
        var emailValid = isValidEmail(email);
        var departmentValid = department && department.length > 0;
        var roleValid = role && role.length > 0;
        var passwordValid = password.length >= 8;
        var confirmPasswordValid = confirmPassword.length >= 8 && password === confirmPassword;

        setFieldState($('#fullname'), fullnameValid);
        setFieldState($('#username'), usernameValid);
        setFieldState($('#email'), emailValid);
        setFieldState($('#department'), departmentValid);
        setFieldState($('#role'), roleValid);
        setFieldState($('#password'), passwordValid);
        setFieldState($('#confirmPassword'), confirmPasswordValid);

        var formValid = fullnameValid && usernameValid && emailValid && departmentValid &&
            roleValid && passwordValid && confirmPasswordValid;

        $submitButton.prop('disabled', !formValid);
        return formValid;
    }

    $requiredFields.on('input change', validateAddUserForm);

    $('#showAddUserPasswords').on('change', function() {
        var inputType = $(this).is(':checked') ? 'text' : 'password';
        $('#password, #confirmPassword').attr('type', inputType);
    });

    $('#addUserModal').on('show.bs.modal', function() {
        $form[0].reset();
        $('#password, #confirmPassword').attr('type', 'password');
        $('#showAddUserPasswords').prop('checked', false);
        $requiredFields.removeClass('is-invalid');
        $submitButton.prop('disabled', true);
    });

    $form.on('submit', function(event) {
        if (!validateAddUserForm()) {
            event.preventDefault();
        }
    });

    $submitButton.prop('disabled', true);
});
</script>


<?php 
include 'includes/footer.php';
?>