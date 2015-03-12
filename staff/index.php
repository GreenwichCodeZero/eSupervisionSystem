<?php
// A dashboard placeholder, demonstrating access to the current user

// Initialise session
session_start();

require '../login-check.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

$currentStaff = $_SESSION['currentUser'];
$userDetails = '';

// Determine permissions of current user
if ($currentStaff['user_type'] === 'staff') {
    // All staff only things here
    $staffDetails = 'Staff: ' . $currentStaff['staff_username'];
    $staffName = $currentStaff['staff_first'] . ' ' . $currentStaff['staff_last'];

    if ($currentUser['staff_authorised'] === '1') {
        // Authorised staff only things here
        $userDetails .= '<li>staff_authorised: yes</li>';
    } else {
        // Unauthorised staff only things here
        $userDetails .= '<li>staff_authorised: no</li>';
    }
}

include '../classes/communication.class.php';
include '../classes/meetings.class.php';
include '../classes/userDetails.class.php';

// $_SESSION['user']['id']
$staff_username = $currentStaff['staff_username']; // (1) = demo staff id
$staff_id = $currentStaff['staff_id'];

// PRINT USER VARIABLES TO TOP OF BROWSER


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
$studentsSupervised->supervisorStudents($staff_id);
$students = $studentsSupervised->getResponse();

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach ($getStaffDetails as $staffDetail) {
    $staffAuthorsied = $staffDetail['staff_authorised'];
}
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
        <ul id="nav-mobile" class="right hide-on-med-and-down">
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
    <div class="row">
        <h4 class="center-align">eSupervision Dashboard</h4>
    </div>
    <div class="row">
        <div class="col s10 offset-s1 m8 offset-m2 l6 offset-l3 center-align">
            <div>
                <?php echo $staffDetails; ?>
            </div>
            <div>
                <?php echo $staffName; ?>
            </div>
        </div>
    </div>
    <div class="row">
		
		<!--  Project students starts here -->
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">List of project students</span>

                    <div class="collection">
                        <?php
                        foreach ($students as $student) {
                            echo "<a class='collection-item' href='#'>" . $student['student_first'] . " " . $student['student_last'] . "</a>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!--  Project students ends here -->
		
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Meeting Summary</span>

                    <p>You have submitted <?php echo $meeting_count; ?> meeting records.</p>
                </div>
                <div class="card-action">
                    <a href="meetings.php" title="View all meetings">View or Request</a>
                </div>
            </div>
        </div>

        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Message Summary</span>

                    <p>You have submitted <?php echo $message_count; ?> messages.</p>

                    <p>You have received <?php echo $received_count; ?> messages.</p>
                </div>
                <div class="card-action">
                    <a href="messages.php" title="View all messages">View or Send</a>
                </div>
            </div>
        </div>
	</div>
</body>

</html>