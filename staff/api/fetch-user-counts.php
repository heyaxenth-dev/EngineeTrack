<?php 

$get_user_counts_query = "SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') AS admin_count,
    (SELECT COUNT(*) FROM users WHERE role = 'faculty') AS faculty_count,
    (SELECT COUNT(*) FROM users WHERE role = 'student') AS student_count";
    $get_user_counts_query .= "";
    $get_user_counts_query .= LIKEBTN_USERS_TABLE;
    $get_user_counts_result = mysqli_query($conn, $get_user_counts_query);
    $user_counts = array();
    while ($row = mysqli_fetch_assoc($get_user_counts_result)) {
        $user_counts[] = $row;
    }
?>