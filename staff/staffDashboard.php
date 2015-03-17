<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
include '../classes/userDetails.class.php';
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/meetings.class.php';

$currentStaff = $_SESSION['currentUser'];
$newStaffId = $_GET['staff'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to student dashboard
    header('Location: /codezero/student/index.php');
} else if ($currentStaff['staff_authorised'] !== '1') {
    // Do not allow access to unauthorised staff
    header('Location: index.php');
}

$newCurrentStaffQ = new UserDetails();
$newCurrentStaffQ->getNewStaffDetails($newStaffId);
$newCurrentStaff = $newCurrentStaffQ->getResponse();

$staffDetails = 'Staff: ' . $newCurrentStaff[0]['staff_username'];
$staffName = $newCurrentStaff[0]['staff_first'] . ' ' . $newCurrentStaff[0]['staff_last'];

$staff_username = $newCurrentStaff[0]['staff_username'];
$staff_id = $newCurrentStaff[0]['staff_id'];

$c = new Communication ();

$c->getAll('message', $staff_username, 'staff');
$messages = $c->getResponse();
$message_count = count($messages);

$c->received($staff_username, 'staff');
$received = $c->getResponse();
$received_count = count($received);

$m = new Meeting ();
$m->getAll($staff_username);
$meetings = $m->getResponse();
$meeting_count = count($meetings);

$studentsSupervised = new UserDetails ();
$studentsSupervised->supervisorStudents($newStaffId);
$students = $studentsSupervised->getResponse();

$noSupervisorQ = new UserDetails ();
$noSupervisorQ->noSupervisor();
$noSupervisors = $noSupervisorQ->getResponse();

$noSecondMarkerQ = new UserDetails ();
$noSecondMarkerQ->noSecondMarker();
$noSecondMarkers = $noSecondMarkerQ->getResponse();

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Dashboard</title>
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
            $(".dropdown-button").dropdown();
        });
    </script>
</head>

<body>
<nav>
    <div class="nav-wrapper green">
        <ul class="right hide-on-med-and-down">
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

            <?php
            if ($currentStaff['staff_authorised'] == 1) {
                echo '<li><a href="search.php">Allocation Search</a></li>
					<li><a href="viewDashboards.php">View Dashboards</a></li>
                    <li><a href="reports.php">Reports</a></li>';
            }
            ?>
            <li>
                <a href="../logout.php" title="Logout">Logout</a>
            </li>
        </ul>
        <ul id="nav-mobile" class="side-nav hide-on-large-only" style="overflow-y: scroll;">
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

            <?php
            if ($currentStaff['staff_authorised'] == 1) {
                echo '<li><a href="search.php">Allocation Search</a></li>
					<li><a href="viewDashboards.php">View Dashboards</a></li>
					<li><a href="reports.php">Reports</a></li>';
            }
            ?>
            <li>
                <a href="../logout.php" title="Logout">Logout</a>
            </li>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>
<div class="container">
    <div class="row">
        <h5 class="center-align">Viewing
            staff: <?php echo $newCurrentStaff[0]['staff_first'] . " " . $newCurrentStaff[0]['staff_last'] . "'s "; ?>
            eSupervision Dashboard</h5>
        <h6 class="center-align"><a href="viewDashboards.php">Back to Selection</a></h6>
    </div>
    <div class="row">
        <div class="col s10 offset-s1 m8 offset-m2 l6 offset-l3 center-align">
            <div>
                <?php echo $staffDetails; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Meeting Summary</span>

                    <p>You have submitted <?php echo $meeting_count; ?> meeting records.</p>
                </div>
                <div class="card-action">
                    <a href="#" title="View all meetings">View or Request</a>
                </div>
            </div>
        </div>

        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Message Summary</span>

                    <p>You have submitted <?php echo $message_count; ?> and received <?php echo $received_count; ?>
                        messages.</p>
                </div>
                <div class="card-action">
                    <a href="#" title="View all messages">View or Send</a>
                </div>
            </div>
        </div>

        <!--  Project students starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Your Project Students</span>
                    </div>

                    <div class="row">
                        <?php foreach ($students as $student) {
                            echo '<div class="col s12 m6 l4">' . $student['student_first'] . ' ' . $student['student_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!--  Project students ends here -->
    </div>
</body>

</html>