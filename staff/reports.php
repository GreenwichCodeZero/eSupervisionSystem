<?php

// Initialise session
session_start();

error_reporting(1);

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

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

$noSupervisorQ = new UserDetails();
$noSupervisorQ->noSupervisor();
$noSupervisors = $noSupervisorQ->getResponse();

$noSecondMarkerQ = new UserDetails();
$noSecondMarkerQ->noSecondMarker();
$noSecondMarkers = $noSecondMarkerQ->getResponse();

$notLoggedin7DaysQ = new Reports();
$notLoggedin7DaysQ->notLoggedIn7Days();
$notLoggedIn7Days = $notLoggedin7DaysQ->getResponse();


$allStudentsQ = new UserDetails();
$allStudentsQ->GetAllStudents();
$allStudents = $allStudentsQ->getResponse();

$notActiveButAssignedToASupervisorQ = new Reports();
$notActiveButAssignedToASupervisorQ->notActiveButAssignedToASupervisor();
$notActiveButAssignedToASupervisors = $notActiveButAssignedToASupervisorQ->getResponse();

$studentSupervisorsQ = new UserDetails();
$studentSecondMarkersQ = new UserDetails();

$test = array();

foreach($allStudents as $student){
    
    $studentSupervisorsQ->getStudentSupervisor($student['student_id']);
    $studentSupervisors = $studentSupervisorsQ->getResponse();

    foreach($studentSupervisors as $stuSupervisorMarkers){
        $a = $stuSupervisorMarkers['staff_id'];

    }

    $studentSecondMarkersQ->getStudentSecondMarker($student['student_id']);
    $studentSecondMarkers = $studentSecondMarkersQ->getResponse();

    foreach($studentSecondMarkers as $stuSecondMarkers){
        $b = $stuSecondMarkers['staff_id'];

    }

    if($a == $b){
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
            if ($staffAuthorsied == 1) {
                echo '<li><a href="search.php">Search</a></li>
                    <li><a href="viewDashboards.php">View dashboards</a></li>
                    <li><a href="reports.php">Reports</a></li>';
            }
            ?>
            <li>
                <a href="../logout.php" title="Logout">Logout</a>
            </li>
        </ul>
		<ul id="nav-mobile" class="side-nav hide-on-large-only">
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
			if ($staffAuthorsied == 1) {
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

                        <?php
                        foreach ($noSupervisors as $noSupervisor) {
                            echo "<br>" . $noSupervisor['student_first'] . " " . $noSupervisor['student_last'];
                        }
                        ?>
                </div>
            </div>
        </div>
        <!--  Students without supervisor ends here -->

        <!--  Students without second marker starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students without a second marker</span>
                        <?php
                        foreach ($noSecondMarkers as $noSecondMarker) {
                            echo "<br>" . $noSecondMarker['student_first'] . " " . $noSecondMarker['student_last'];
                        }
                        ?>
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
<?php
        $stuDetailsQ = new UserDetails();

        foreach($test as $id){
            $stuDetailsQ->GetStudentDetails($id);
            $stuDetails = $stuDetailsQ->getResponse();

            foreach($stuDetails as $stu){
                echo "<br>" . $stu['student_first'] .  " " . $stu['student_last'];
            }
        }
?>
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
                    <span class="card-title green-text">Students who haven't logged in for the past 7 days</span>
                        <?php
                            foreach($notLoggedIn7Days as $notLoggedIn){
                               echo '<br>' . $notLoggedIn['student_first'] . " " . $notLoggedIn['student_last'];
                            }
                        ?>
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
                    <span class="card-title green-text">Inactive students assigned to a supervisor</span>

                  <?php
                    foreach($notActiveButAssignedToASupervisors as $inactiveStudent){
                        echo "<br>" . $inactiveStudent['student_first'] . " " . $inactiveStudent['student_last'];
                    }

                  ?>
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