<?php
// A dashboard placeholder, demonstrating access to the current user

// Initialise session
session_start();

require 'login-check.php';

$currentUser = $_SESSION['currentUser'];
$userDetails = '';

// Determine permissions of current user
if ($currentUser['user_type'] === 'staff') {
    // All staff only things here
    $userDetails = '<li>staff_first: ' . $currentUser['staff_first'] . '</li>
                    <li>staff_last: ' . $currentUser['staff_last'] . '</li>
                    <li>staff_email: ' . $currentUser['staff_email'] . '</li>
                    <li>staff_banner_id: ' . $currentUser['staff_banner_id'] . '</li>
                    <li>staff_active: ' . $currentUser['staff_active'] . '</li>
                    <li>user_type: ' . $currentUser['user_type'] . '</li>';

    if ($currentUser['staff_authorised'] === '1') {
        // Authorised staff only things here
        $userDetails .= '<li>staff_authorised: yes</li>';
    } else {
        // Unauthorised staff only things here
        $userDetails .= '<li>staff_authorised: no</li>';
    }
} else {
    // Student only things here
    $userDetails = '<li>student_first: ' . $currentUser['student_first'] . '</li>
                    <li>student_last: ' . $currentUser['student_last'] . '</li>
                    <li>student_email: ' . $currentUser['student_email'] . '</li>
                    <li>student_banner_id: ' . $currentUser['student_banner_id'] . '</li>
                    <li>student_active: ' . $currentUser['student_active'] . '</li>
                    <li>user_type: ' . $currentUser['user_type'] . '</li>';
}

?>

<p>Dashboard placeholder. Must be logged in to see this.</p>

<p><a href="logout.php" title="Logout">Logout</a></p>

<ul>
    <?php echo $userDetails; ?>
</ul>

