<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
include '../classes/userDetails.class.php';
include '../classes/security.class.php';
include '../classes/reports.class.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
}

$userDetails = new UserDetails();
$reports = new Reports();

$userDetails->isStaffAuthorised($staff_id);
$getStaffDetails = $userDetails->getResponse();

$userDetails->noSupervisor();
$noSupervisors = $userDetails->getResponse();

$userDetails->noSecondMarker();
$noSecondMarkers = $userDetails->getResponse();

$reports->StaffOver70PercentMeetingsDeclined();
$staffMeetingsDeclined = $reports->getResponse();

$reports->StudentsNoMeetingsPast2Weeks();
$studentsNoRecentMeetings = $reports->getResponse();

$reports->notLoggedIn7Days();
$notLoggedIn7Days = $reports->getResponse();

$userDetails->GetAllStudents();
$allStudents = $userDetails->getResponse();

$reports->notActiveButAssignedToASupervisor();
$notActiveButAssignedToASupervisors = $reports->getResponse();

$test = array();

foreach ($allStudents as $student) {

    $userDetails->getStudentSupervisor($student['student_id']);
    $studentSupervisors = $userDetails->getResponse();

    foreach ($studentSupervisors as $stuSupervisorMarkers) {
        $a = $stuSupervisorMarkers['staff_id'];
    }

    $userDetails->getStudentSecondMarker($student['student_id']);
    $studentSecondMarkers = $userDetails->getResponse();

    foreach ($studentSecondMarkers as $stuSecondMarkers) {
        $b = $stuSecondMarkers['staff_id'];
    }

    if ($a == $b) {
        array_push($test, $student['student_username']);
    }
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

    <h4 class="green-text">Reports</h4>

    <div class="row">
        <!-- Students without a supervisor starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Students without a supervisor</span>
                    </div>

                    <div class="row">
                        <?php foreach ($noSupervisors as $noSupervisor) {
                            echo '<div class="col s12 m6 l4">' . $noSupervisor['student_first'] . ' ' . $noSupervisor['student_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Students without a supervisor ends here -->

        <!-- Students without a second marker starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Students without a second marker</span>
                    </div>

                    <div class="row">
                        <?php foreach ($noSecondMarkers as $noSecondMarker) {
                            echo '<div class="col s12 m6 l4">' . $noSecondMarker['student_first'] . ' ' . $noSecondMarker['student_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Students without a second marker ends here -->

        <!-- Students with no meeting requests within 2 weeks starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Students with no meeting requests within 2 weeks</span>
                    </div>

                    <div class="row">
                        <?php foreach ($studentsNoRecentMeetings as $student) {
                            echo '<div class="col s12 m6 l4">' . $student['student_first'] . ' ' . $student['student_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!--  Students with no meeting requests within 2 weeks ends here -->

        <!-- Students with the same supervisor and second marker starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Students with the same supervisor and second marker</span>
                    </div>

                    <div class="row">
                        <?php foreach ($test as $id) {
                            $userDetails->GetStudentDetails($id);
                            $stuDetails = $userDetails->getResponse();

                            foreach ($stuDetails as $stu) {
                                echo '<div class="col s12 m6 l4">' . $stu['student_first'] . ' ' . $stu['student_last'] . '</div>';
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Students with the same supervisor and second marker ends here -->

        <!-- Students who haven't logged in for the past 7 days starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Students who haven't logged in for the past 7 days</span>
                    </div>

                    <div class="row">
                        <?php foreach ($notLoggedIn7Days as $notLoggedIn) {
                            echo '<div class="col s12 m6 l4">' . $notLoggedIn['student_first'] . ' ' . $notLoggedIn['student_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Students who haven't logged in for the past 7 days ends here -->

        <!-- Staff who have declined more than 70% of meeting requests starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span
                            class="card-title green-text">Staff who have declined more than 70% of meeting requests</span>
                    </div>

                    <div class="row">
                        <?php foreach ($staffMeetingsDeclined as $staff) {
                            echo '<div class="col s12 m6 l4">' . $staff['staff_first'] . ' ' . $staff['staff_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Staff who have declined more than 70% of meeting requests ends here -->

        <!-- Inactive students assigned to a supervisor starts here -->
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-left: 0; margin-right: 0;">
                        <span class="card-title green-text">Inactive students assigned to a supervisor</span>
                    </div>

                    <div class="row">
                        <?php foreach ($notActiveButAssignedToASupervisors as $inactiveStudent) {
                            echo '<div class="col s12 m6 l4">' . $inactiveStudent['student_first'] . ' ' . $inactiveStudent['student_last'] . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inactive students assigned to a supervisor ends here -->

    </div>

</div>
</body>

</html>