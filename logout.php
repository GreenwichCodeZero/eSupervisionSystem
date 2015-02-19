<<<<<<< HEAD
<?php
// Logs the user out and redirects to the home page

error_reporting(0);

// Initialise session
session_start();

// Destroy session
session_destroy();

// Redirect to home page
header('Location: index.php');
=======
<?php
// Logs the user out and redirects to the home page

error_reporting(0);

// Initialise session
session_start();

// Destroy session
session_destroy();

// Redirect to home page
header('Location: index.php');
>>>>>>> branchFeature-US18
?>