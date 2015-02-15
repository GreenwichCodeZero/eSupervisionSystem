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
                <a href="meetings.php">Meetings</a>
            </li>
            <li>
                <a href="messages.php">Messages</a>
            </li>
            <li>
                <a href="blogs.php">Blog</a>
            </li>
            <li>
                <a href="uploads.php">Uploads</a>
            </li>
        </ul>
    </div>
</nav>
<div class="container">
    <div class="row">
        <h5 class="center-align">eSupervision Dashboard</h5>
    </div>
    <div class="row">
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Student Summary</span>

                    <p>
                        <?php echo $userDetails; ?>
                    </p>
                </div>
                <div class="card-action">
                    <a href="../logout.php" title="Logout">Logout</a>
                </div>
            </div>
        </div>
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Supervisor Details</span>

                    <p>
                        Supervisor: <?php echo "<a href=" . '"' . $supervisor[0]['staff_profile_link'] . '" target="_blank">' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . "</a>"; ?>
                    </p>

                    <p>
                        Second Marker: <?php echo "<a href=" . '"' . $secondMarker[0]['staff_profile_link'] . '" target="_blank">' . $secondMarker[0]['staff_first'] . ' ' . $secondMarker[0]['staff_last'] . "</a>"; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m6 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Meeting Summary</span>

                    <p>You have submitted <?php echo $meeting_count; ?> meeting records.</p>
                </div>
                <div class="card-action">
                    <a href="meetings.php" title="View all meetings">View All</a>
                    <a href="#" title="Request new meeting">Request</a>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Message Summary</span>

                    <p>You have submitted <?php echo $message_count; ?> messages.</p>
                </div>
                <div class="card-action">
                    <a href="messages.php" title="View all messages">View All</a>
                    <a href="#" title="Write new message">New</a>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Blog Summary</span>

                    <p>You have submitted <?php echo $blog_count; ?> blog posts.</p>
                </div>
                <div class="card-action">
                    <a href="blogs.php" title="View all blogs">View All</a>
                    <a href="#" title="Write new blog">Write</a>
                </div>
            </div>
        </div>

    </div>
</div>
</body>

</html>