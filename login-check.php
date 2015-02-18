<?php
// Require this on every page where the user should be logged in, will redirect if not

// Redirect direct access to this file
// Source: http://board.phpbuilder.com/showthread.php?10263469#post10986948
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // File requested directly, redirect to home page
    header('Location: /codezero/index.php');
} else {
    // Checks user is logged in and redirects if necessary
    if (!isset($_SESSION['currentUser'])) {
        // User is not logged in
        // Redirect to home page
        header('Location: /codezero/index.php');
    }
}
?>