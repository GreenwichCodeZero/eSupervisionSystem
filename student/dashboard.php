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
$user_id = $currentUser['student_id']; // (1) = demo student id
$user_user = $currentUser['student_username']; // (1) = demo student id

// PRINT USER VARIABLES                                                                                                                                                                     TO TOP OF BROWSER
// print_r ($currentUser) ;

$c = new Communication ();

$c->getAll('blog', $user_user);
$blogs = $c->getResponse();
$blog_count = count($blogs);

$c->getAll('message', $user_user);
$messages = $c->getResponse();
$message_count = count($messages);

$c->received('message', $user_user);
$received = $c->getResponse();
$received_count = count($received);

$m = new Meeting ();
$m->getAll(null, $user_user);
$meetings = $m->getResponse();
$meeting_count = count($meetings);

$u = new UserDetails ();
$u->studentSuper($user_id);
$supervisor = $u->getResponse();

$u2 = new UserDetails ();
$u2->studentSM($user_id);
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
                    <a href="messages.php">Communication</a>
                </li>
                <li>
                    <a href="meetings.php">Meetings</a>
                </li>
                <li>
                    <a href="blogs.php">Blog/Diary</a>
                </li>
                <li>
                    <a href="uploads.php">Project Uploads</a>
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
                        Supervisor:<?php echo "<a href=" . '"' . $supervisor[0]['staff_profile_link'] . '" target="_blank">' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . "</a>"; ?>
                    </p>

                    <p>
                        Second Marker:<?php echo "<a href=" . '"' . $secondMarker[0]['staff_profile_link'] . '" target="_blank">' . $secondMarker[0]['staff_first'] . ' ' . $secondMarker[0]['staff_last'] . "</a>"; ?>
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

    </div>
</div>


 <!-- Start New Message Modal -->
  <div id="newMessageModal" class="modal modal-fixed-footer">
    <form method="post" action = "messages.php">
    <div class="modal-content">
      <h4>Send a message to supervisor</h4>
      
        <textarea name="communication_body"></textarea>
        <input type="hidden" name="communication_action" value = "sendmessage" />
        <input type="hidden" name="communication_from_id" value="<?php echo $currentUser['student_username']; ?>" ?>
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
        <input type="hidden" name="communication_from_id" value="<?php echo $currentUser['student_username']; ?>" ?>
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