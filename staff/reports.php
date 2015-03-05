<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Reports</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".button-collapse").sideNav();
            $('select').material_select();
        });
    </script>
</head>
<body>

<nav>
    <div class="nav-wrapper green">
        <ul id="nav-mobile" class="side-nav">
            <li>
                <a href="index.php">Dashboard</a>
            </li>
            <li>
                <a href="meetings.php">Meetings</a>
            </li>
            <li>
                <a href="messages.php">Messages</a>
            </li>
            <li>
                <a href="blogs.php">Blog</a>
            </li>
            <li>
                <a href="uploads.php">Project Uploads</a>
            </li>
            <?php if ($currentStaff['staff_authorised'] == 1) { ?>
                <li>
                    <a href="search.php">Search</a>
                </li>
                <li>
                    <a href="viewDashboards.php">View dashboards</a>
                </li>
            <?php } ?>
            <li>
                <a href="reports.php">Reports</a>
            </li>
            <li>
                <a href="../logout.php" title="Logout">Logout</a>
            </li>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>

<div class="container">

    Reports placeholder

</div>
</body>

</html>