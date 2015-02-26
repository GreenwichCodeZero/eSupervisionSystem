<?php
// A dashboard placeholder, demonstrating access to the current user

// Initialise session
session_start();

require '../login-check.php';

$currentStaff = $_SESSION['currentUser'];
$userDetails = '';

// Determine permissions of current user
if ($currentStaff['user_type'] === 'staff') {
    // All staff only things here
    $userDetails = '<li>staff_first: ' . $currentStaff['staff_first'] . '</li>
                    <li>staff_last: ' . $currentStaff['staff_last'] . '</li>
                    <li>staff_username: ' . $currentStaff['staff_username'] . '</li>
                    <li>staff_banner_id: ' . $currentStaff['staff_banner_id'] . '</li>
                    <li>staff_active: ' . $currentStaff['staff_active'] . '</li>
                    <li>user_type: ' . $currentStaff['user_type'] . '</li>';

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

$c->getAll('blog', $staff_username);
$blogs = $c->getResponse();
$blog_count = count($blogs);

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

$u = new UserDetails ();
$u->studentSuper($staff_id);
$supervisor = $u->getResponse();

$u2 = new UserDetails ();
$u2->studentSM($staff_id);
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

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach($getStaffDetails as $staffDetail){
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
            if($staffAuthorsied == 1){
            
            echo '<li>
                <a href="search.php">Search</a>
            </li>';
        }
        ?>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
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
                    <p>You have received <?php echo $received_count; ?> messages.</p>
                </div>
                <div class="card-action">
                    <a href="messages.php" title="View all messages">View All</a>
                    <a class="waves-effect waves-light btn modal-trigger" href="#newMessageModal" title="Write new message">Message </a>
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
                    <a class="waves-effect waves-light btn modal-trigger" href="#newBlogModal" title="Write new blog post">New Post</a>
                </div>
            </div>
        </div>




        <!-- new stuff -->

 <!--  Project students starts here -->
        <div class="col s12 m12 l4">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">List of project students</span>
                    <br>
                    <?php
                    foreach ($students as $student) {
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
                        <input type="search" name="searchForStudent" id="searchForStudent"
                               placeholder="Enter student name...">
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

                    foreach ($noSupervisors as $noSupervisor) {
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

                        foreach ($noSecondMarkers as $noSecondMarker) {
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

        <!-- end of new -->

    </div>
</div>


<!-- Start New Message Modal -->
<div id="newMessageModal" class="modal modal-fixed-footer">
    <form method="post" action = "messages.php">
        <div class="modal-content">
            <h4>Send a message to supervisor</h4>

            <textarea name="communication_body"></textarea>
            <input type="hidden" name="communication_action" value = "sendmessage" />
            <input type="hidden" name="communication_from_id" value="<?php echo $currentStaff['student_username']; ?>" ?>
            <input type="hidden" name="communication_to_id" value = "<?php echo $supervisor[0]['staff_username']; ?>" />
        </div>
        <div class="modal-footer">

            <button class="waves-effect waves-green btn-flat ">Submit</button>
        </div>
    </form>
</div>
<!-- End New Message Modal -->

<!-- Start New Message Modal -->
<div id="newBlogModal" class="modal modal-fixed-footer">
    <form method="post" action = "blogs.php">
        <div class="modal-content">
            <h4>New blog post</h4>

            <textarea name="communication_body"></textarea>
            <input type="hidden" name="communication_action" value="posttoblog" />
            <input type="hidden" name="communication_from_id" value="<?php echo $currentStaff['student_username']; ?>" ?>
            <input type="hidden" name="communication_to_id" value="blog" />
        </div>
        <div class="modal-footer">

            <button class="waves-effect waves-green btn-flat ">Submit</button>
        </div>
    </form>
</div>
<!-- End New Message Modal -->
</body>
<script>
    $(document).ready(function(){
        $('.modal-trigger').leanModal();
    });
</script>
</html>