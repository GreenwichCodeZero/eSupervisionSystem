<?php
// A dashboard placeholder, demonstrating access to the current user

// Initialise session
session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
$userDetails = '';

// Determine permissions of current user
if ($currentUser['user_type'] === 'staff') {
    // All staff only things here
    $userDetails = '<li>staff_first: ' . $currentUser['staff_first'] . '</li>
                    <li>staff_last: ' . $currentUser['staff_last'] . '</li>
                    <li>staff_username: ' . $currentUser['staff_username'] . '</li>
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
    $userDetails = '<b>' . $currentUser['student_first'] . ' ' . $currentUser['student_last'] . '</b> (' . $currentUser['student_username'] . ')
                    <p>Banner ID: ' . $currentUser['student_banner_id'] . '</p>';
}


include '../classes/communication.class.php';
include '../classes/meetings.class.php';
include '../classes/userDetails.class.php';

// $_SESSION['user']['id']
$stu_id = $currentUser['student_id']; // (1) = demo student id
$staff_id = $currentUser['staff_id'];

$c = new Communication ();

$c->getAll('blog', 'student', $stu_id);
$blogs = $c->getResponse();
$blog_count = count($blogs);

$c->getAll('message', 'student', $stu_id);
$messages = $c->getResponse();
$message_count = count($messages);

$m = new Meeting ();
$m->getAll(null, $stu_id);
$meetings = $m->getResponse();
$meeting_count = count($meetings);

$u = new UserDetails ();
$u->studentSuper($stu_id);
$supervisor = $u->getResponse();

$u2 = new UserDetails ();
$u2->studentSM($stu_id);
$secondMarker = $u2->getResponse();

$studentsSupervised = new UserDetails ();
$studentsSupervised->supervisorStudents($staff_id);
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

    <!--<link href="css/styles.css" rel="stylesheet" type="text/css"/>-->
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
        <ul id="nav-mobile" class="side-nav">
            <li>
                <a href="dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="#">Meetings</a>
            </li>
            <li>
                <a href="#">Messages</a>
            </li>
            <li>
                <a href="#">Blog</a>
            </li>
            <li>
                <a href="#">Uploads</a>
            </li>
        </ul>
    </div>
</nav>
<div class="container">
    <div class="row">
        <h5 class="center-align">eSupervision Dashboard</h5>
    </div>

<!--  Staff summary starts here -->
    <div class="row">
        <div class="col s12 m12 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Staff Summary</span>

                    <p>
                        <?php echo $userDetails; ?>
                    </p>
                </div>
                <div class="card-action">
                    <a href="../logout.php" title="Logout">Logout</a>
                </div>
            </div>
        </div>
<!--  Staff summary ends here -->

<!--  Project students starts here -->
        <div class="col s12 m12 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">List of project students</span>
                    <br>
                   <?php
                   foreach($students as $student){
                    echo $student['student_first'] . " " . $student['student_last'] . "<br>";
                   }
                   ?>
                </div>
                <div class="card-action">
                    <a href="#" title="View all students">View all</a>
                </div>
            </div>
        </div>
<!--  Project students ends here -->

<!--  Search for students starts here -->
        <div class="col s12 m12 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Search for student</span>
                    <form>
                   <input type="search" name="searchForStudent" id="searchForStudent" placeholder="Enter student name...">
                   <input type="submit" value="Search">
                   </form>
                </div>
            </div>
        </div>
    </div>
<!--  Search for students ends here -->

<!--  Students without supervisor starts here -->

    <div class="row">
        <div class="col s12 m6 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students without a supervisor</span>
                    <br>
                    <?php

                    foreach($noSupervisors as $noSupervisor){
                        echo $noSupervisor['student_first'] . " " . $noSupervisor['student_last'] . "<br>";
                    }
                    ?>
                </div>
                <div class="card-action">
                    <a href="#" title="View all students without a supervisor">View all</a>
                </div>
            </div>
        </div>
<!--  Students without supervisor ends here -->

<!--  Students without second marker starts here -->

    <div class="row">
        <div class="col s12 m6 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students without a second marker</span>
                    <br>
                    <?php

                    foreach($noSecondMarkers as $noSecondMarker){
                        echo $noSecondMarker['student_first'] . " " . $noSecondMarker['student_last'] . "<br>";
                    }
                    ?>
                </div>
                <div class="card-action">
                    <a href="#" title="View all students without a second marker">View all</a>
                </div>
            </div>
        </div>
<!--  Students without second marker ends here -->

    </div>
</div>
</body>

</html>