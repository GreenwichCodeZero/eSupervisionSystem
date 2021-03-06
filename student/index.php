<?php
// Student dashboard page

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';

$currentStudent = $_SESSION['currentUser'];
$studentDetails = $studentBannerId = $studentName = $studentProgramme = '';

// Determine permissions of current user
if ($currentStudent['user_type'] === 'staff') {
    // Redirect to staff dashboard
    header('Location: /codezero/staff/index.php');
} else {
    // Student only things here
     $studentDetails = 'Student: '. $currentStudent['student_username'];
	 $studentBannerId= 'BannerID: '. $currentStudent['student_banner_id'];
	 $studentName = $currentStudent['student_first'] . ' ' . $currentStudent['student_last'];
     $studentProgramme = $currentStudent['programme_title'];
}

include '../classes/communication.class.php';
include '../classes/meetings.class.php';
include '../classes/userDetails.class.php';

$user_id = $currentStudent['student_id'];
$user_user = $currentStudent['student_username'];

$c = new Communication ();

$c->getAll('blog', $user_user, 'student');
$blogs = $c->getResponse();
$blog_count = count($blogs);

$c->getAll('message', $user_user, 'student');
$messages = $c->getResponse();
$message_count = count($messages);

$c->received($user_user, 'student');
$received = $c->getResponse();
$received_count = count($received);

$m = new Meeting ();
$m->getAll($user_user);
$meetings = $m->getResponse();
$meeting_count = count($meetings);

$u = new UserDetails ();
$u->getStudentSupervisor($user_id);
$supervisor = $u->getResponse();

$u2 = new UserDetails ();
$u2->getStudentSecondMarker($user_id);
$secondMarker = $u2->getResponse();

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
			$('.modal-trigger').leanModal();
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
					<a href="submissions.php">Project Uploads</a>
				</li>
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
					<a href="submissions.php">Project Uploads</a>
				</li>
				<li>
					<a href="../logout.php" title="Logout">Logout</a>
				</li>
			</ul>
			<a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
		</div>
	</nav>
<div class="container">
    <h4 class="center-align">eSupervision Dashboard</h4>
	<div class="row">
		<div class="left-align col s4">
			<p>
				<?php echo $studentDetails; ?> 
			</p>
			<p>
				<?php echo $studentBannerId; ?> 
			</p>
		</div>
		<div class="center-align col s4">
			<p id="userName">
				<?php echo $studentName; ?>
			</p>

            <?php echo $studentProgramme; ?>
		</div>
		<div class="right-align col s4">
			<p>
				Supervisor: <?php echo "<a href=" . '"' . $supervisor[0]['staff_profile_link'] . '" target="_blank">' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . "</a>"; ?>
			</p>
			<p>
				Second Marker: <?php echo "<a href=" . '"' . $secondMarker[0]['staff_profile_link'] . '" target="_blank">' . $secondMarker[0]['staff_first'] . ' ' . $secondMarker[0]['staff_last'] . "</a>"; ?>
			</p>
		</div>
	</div>
    <div class="row">
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Meeting Summary</span>
                    <p>You have submitted <?php echo $meeting_count; ?> meeting records.</p>
                </div>
                <div class="card-action">
                    <a href="meetings.php" title="View all meetings">View All or Request</a>
                </div>
            </div>
        </div>
		<div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Blog Summary</span>
                    <p>You have submitted <?php echo $blog_count; ?> blog posts.</p>
                </div>
                <div class="card-action">
                    <a href="blogs.php" title="View all blogs">View All or Add New</a>
                </div>
            </div>
        </div>
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Message Summary</span>
                    <p>You have submitted <?php echo $message_count; ?> messages.</p>
                    <p>You have received <?php echo $received_count; ?> messages.</p>
                </div>
                <div class="card-action">
                    <a href="messages.php" title="View all messages">View All or Submit New</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start New Message Modal -->
<div id="newMessageModal" class="modal modal-fixed-footer">
    <form method="post" action="messages.php">
        <div class="modal-content">
            <h4>Send a message to supervisor</h4>

            <textarea name="communication_body"></textarea>
            <input type="hidden" name="communication_action" value="sendmessage"/>
            <input type="hidden" name="communication_from_id" value="<?php echo $currentStudent['student_username']; ?>"
                   ?>
            <input type="hidden" name="communication_to_id" value="<?php echo $supervisor[0]['staff_username']; ?>"/>
        </div>
        <div class="modal-footer">

            <button class="waves-effect waves-green btn-flat ">Submit</button>
        </div>
    </form>
</div>
<!-- End New Message Modal -->


<!-- Start New Message Modal -->
<div id="newBlogModal" class="modal modal-fixed-footer">
    <form method="post" action="blogs.php">
        <div class="modal-content">
            <h4>New blog post</h4>

            <textarea name="communication_body"></textarea>
            <input type="hidden" name="communication_action" value="posttoblog"/>
            <input type="hidden" name="communication_from_id" value="<?php echo $currentStudent['student_username']; ?>"
                   ?>
            <input type="hidden" name="communication_to_id" value="blog"/>
        </div>
        <div class="modal-footer">

            <button class="waves-effect waves-green btn-flat ">Submit</button>
        </div>
    </form>
</div>
<!-- End New Message Modal -->
</body>
</html>