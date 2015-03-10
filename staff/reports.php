<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
include '../classes/userDetails.class.php';
include '../classes/security.class.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
}

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

$noSupervisorQ = new UserDetails();
$noSupervisorQ->noSupervisor();
$noSupervisors = $noSupervisorQ->getResponse();

$noSecondMarkerQ = new UserDetails();
$noSecondMarkerQ->noSecondMarker();
$noSecondMarkers = $noSecondMarkerQ->getResponse();
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
     <?php
            if ($getStaffDetails[0]['staff_authorised'] == 1) {
                echo '<li><a href="search.php">Search</a></li>
                    <li><a href="viewDashboards.php">View dashboards</a></li>
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

    
    <h1>Reports</h1>
    <div class="row">
    <!--  Students without supervisor starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students without a supervisor</span>

                    <div class="collection">
                        <?php
                        foreach ($noSupervisors as $noSupervisor) {
                            echo "<a class='collection-item' href='#'>" . $noSupervisor['student_first'] . " " . $noSupervisor['student_last'] . "</a>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without supervisor ends here -->

        <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students without a second marker</span>

                    <div class="collection">
                        <?php
                        foreach ($noSecondMarkers as $noSecondMarker) {
                            echo "<a class='collection-item' href='#'>" . $noSecondMarker['student_first'] . " " . $noSecondMarker['student_last'] . "</a>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->
    </div>

    <div class="row">
            <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students with no meeting requests within 2 weeks</span>

                    <div class="collection">
                        Display info here
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->

                    <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students with the same supervisor and second marker</span>

                    <div class="collection">
                        Display info here
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->
    </div>


        <div class="row">
            <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students who haven't logged in for 7 days</span>

                    <div class="collection">
                        Display info here
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->

                    <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Staff who have declined more then 70% of meeting requests</span>

                    <div class="collection">
                        Display info here
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->
    </div>


        <div class="row">
            <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">New report here</span>

                    <div class="collection">
                        Display info here
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->

                    <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">New report here</span>

                    <div class="collection">
                        Display info here
                    </div>
                </div>
            </div>
        </div>
        <!--  Students without second marker ends here -->
    </div>


</div>
</body>

</html>