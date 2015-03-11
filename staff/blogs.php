<?php

session_start();

require '../login-check.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

$currentStaff = $_SESSION['currentUser'];

include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/comment.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';


// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$staff_username = $currentStaff['staff_username'];


$com = new Communication ();

if ($_POST['comment_action']) {
    $cmm = new Comment ();
    $el = new errorList ();

    try { $cmm->insert ( $staff_username, $_GET['sid'] ); }
    catch (Exception $e){
       $el->newList()->type('error')->message ($e->getMessage ())->go('blogs.php?sid='.$_GET['sid']);
        exit;
    }

    $el->newList()->type('success')->message($cmm->getResponse())->go('blogs.php?sid='.$_GET['sid']);
    exit;

}

// Determine which messages to display
if ($_GET['sid']) {
    // Filter sent messages by student username (sid)
    $com->getAll('blog', $staff_username, 'staff', $_GET['sid']);

    // Get messages and count
    $blogs = $com->getResponse();
    $blog_count = count($blogs);
} else {
    $blog_count = -1;
}

// Get allocated students
$u = new UserDetails ();
$u->GetAllocatedStudents($staff_username);
$students = $u->getResponse();

// Is staff authorised
$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach($getStaffDetails as $staffDetail){
    $staffAuthorsied = $staffDetail['staff_authorised'];
}
// End is staff authorised
?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervison - Blog</title>
   <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
	<script>
        function toggleForm(elemID, newButtonID) {
            $(elemID).toggle();
            $(newButtonID).toggle();
        };

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

<div class="row">
            <?php
                $el = new errorList ();
                if ($el->exists ()){
                    ?>
                    <p style="border: thin #7CCD7C solid; padding: 10px; background:#E0EEE0;">
                   <?php echo $el->getResponse (); ?>
                    </p>
                   <?
                }
            ?>
</div>

  <div class="row">


        <div class="col s10 m12 offset-s1 card">


            <div class="card-content">
                <span class="card-title green-text">Student Blog History</span>
                <?php if ($blog_count > 0) {
                    // Get student name
                    $ud = new UserDetails ();
                    $ud->GetStudentDetails($_GET['sid']);
                    $student = $ud->getResponse();

                    if ( !isset($_GET['sid']) ) {
                        echo '<p>Your allocated students have submitted '. $blog_count .' blog posts collectively.</p>';
                    } else {

                        echo '<p>'. $student[0]['student_first'] . ' ' . $student[0]['student_last'] . ' has submitted '. $blog_count .' blog posts.</p>';
                    }


                } ?>

                <div class="row">
                    <!-- STUDENT FILTER FORM START -->
                    <form id="communication_filter" action="" method="GET">
                        <div class="col s12 m9">
                            <label for="communication_student_id_filter">Select a student</label>
                            <select name="sid" id="communication_student_id_filter">
                                <option value="" disabled="disabled" selected="selected">Choose...</option>
                                <?php foreach ($students as $stu) {
                                    echo '<option value="' . $stu['student_username'] . '"' . (($_GET['sid'] == $stu['student_username']) ? 'selected="selected"' : '') . '>' . $stu['student_first'] . ' ' . $stu['student_last'] . ' (' . $stu['student_username'] . ') </option>';
                                } ?>
                            </select>
                        </div>
                        <div class="input-field col s12 m3">

                            <button type="submit"
                                    class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                                Filter
                            </button>
                        </div>
                    </form>
                    <!-- STUDENT FILTER FORM END -->
                </div>

                <?php if ($blog_count > 0) {
                    $count = 0;
                    foreach ($blogs as $b) {
                    ++$count;
                        ?>

                        <ul class="collection">
                            <li class="collection-item" <?php echo ($b['communication_from_id'] == $staff_username) ? 'style="background-color: #fafafa;"' : '' ?> >

                            <?php if ($b['communication_comment_id'] == 0){ ?>
                                <a onClick="toggleForm('#sendMessage<?php echo $count; ?>', '#newMessage');" class="c_right_align" id="newMessage<?php echo $count; ?>">
                                    <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Write a comment
                                    </div>
                                </a>
                                <?php } ?>

                                <form action="readfile.php" method="POST">
                                    <p>
                                        <span class="green-text">
                                            <b>
                                                <?php if ($b['communication_from_id'] == $staff_username) {
                                                    // Message is from current staff user
                                                    echo 'Me';
                                                } else {
                                                    // Message is from student
                                                    echo $b['student_first'] . " " . $b['student_last'];
                                                } ?>
                                            </b>
                                        </span>
                                        &#8212;

                                        <?php
                                        // Pretty format the date
                                        $date = strtotime($b['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($b['communication_time_added'], 0, -3); ?>
                                    </p>

                                    <p>
                                        <?php echo $b['communication_body']; ?>
                                    </p>

                                    <?php
                                    if ($b['communication_file_id'] > 0) { ?>
                                        <hr/>
                                        <p>
                                            <input type="hidden" name="file_id"
                                                   value="' . $b['communication_file_id'] . '"/>
                                            <button
                                                class="waves-effect waves-teal waves-light green btn-flat white-text"
                                                style="margin-bottom: 0; margin-top: 15px;">
                                                View File<i class="mdi-editor-attach-file right"></i></button>
                                        </p>
                                    <?php } ?>
                                </form>

                                        <!-- MESSAGE SECTION START-->


                            <?php if ($b['communication_comment_id'] > 0){

                                $cmm1 = new Comment ();
                                $cmm1->getComment ($b['communication_comment_id']);
                                $comment = $cmm1->getResponse ();
                            ?>

                            <!--  COMMENT HTML START -->
							<hr />
                            <p class="grey lighten-3">
                            <b>
                                Comment from <?php echo $comment_staff = ($comment['comment_staff_id'] == $staff_username) ? "me" :  $comment['comment_staff_id']; ?>

                                <?php
                                // Pretty format the date
                                    $date = strtotime($comment['comment_date_added']);
                                    $prettyDate = date('l j F Y', $date);

                                    // Output date and time
                                    echo $prettyDate . ', ' . substr($comment['comment_time_added'], 0, -3);
                                ?>
                             </b>
                             </p>

                            <p class="grey lighten-3"><?php echo $comment['comment_body']; ?></p>

                            <!--  COMMENT HTML END -->

                            <?
                            } else {

                            ?>
                                <!-- NEW COMMENT SECTION START-->
                                <div class="row" id="sendMessage<?php echo $count; ?>">
                                    <div class="col s12">
                                            <i class="small mdi-content-clear c_right-align"
                                               onclick="toggleForm('#sendMessage<?php echo $count; ?>', '#newMessage<?php echo $count; ?>');"></i>

                                            <div class="card-content">

                                                <form id="communication" method="POST"
                                                      action=""
                                                      enctype="multipart/form-data">
                                                    <input type='hidden' name='comment_action' value='newcomment'/>
                                                    <input type="hidden" name="comment_from_id" value="<?php echo $staff_username; ?>">
                                                    <input type='hidden' name='comment_student_id' value='<?php echo $_GET['sid']; ?>'/>
                                                    <input type='hidden' name='comment_communication_id' value='<?php echo $b['communication_id']; ?>'/>


                                                    <div class="input-field col s12">
                                                        <label for="comment_body">Your comment:</label>
                                                        <textarea class="materialize-textarea" name="comment_body"
                                                                  id="communication_body"></textarea>
                                                    </div>

                                                    <div class="input-field col s12">
                                                        <button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                                                            Submit
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                </div>
                                <!-- NEW COMMENT SECTION END-->



                                <?
                            }

                            ?>





                            </li>
                        </ul>




                    <?php }
                } else if ($blog_count == 0) {
                    // No messages found for current student
                    echo '<ul class="collection"><li class="collection-item">No posts to display</li></ul>';
                } ?>
            </div>
        </div>
        <!--MESSAGING SECTION END-->
    </div>

</div>
<!-- end container -->
</body>

</html>
