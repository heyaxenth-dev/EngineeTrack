<?php
session_start();

// Decide login page before clearing session.
$current_role = strtolower((string) ($_SESSION['user_role'] ?? ''));
$redirect_to = '../authentication/admin-login.php';

if (!in_array($current_role, ['admin', 'administrator'], true)) {
    $redirect_to = '../authentication/staff-login.php';
}

// Clear all session data and destroy it.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: ' . $redirect_to);
exit();
?>
<?php
session_start();
if (isset($_SESSION['is_authenticated'])) {
    $_SESSION['status'] = 'Logged Out Successfully!';
    $_SESSION['status_text'] = 'You have been logged out.';
    $_SESSION['status_code'] = 'success';
    $_SESSION['status_btn'] = 'Done';
    unset($_SESSION['is_authenticated'], $_SESSION['admin_id'], $_SESSION['user_id'], $_SESSION['username'], $_SESSION['email'], $_SESSION['firstname'], $_SESSION['lastname'], $_SESSION['logged'], $_SESSION['logged_icon']);
}
header('Location: ./login.php');
exit;