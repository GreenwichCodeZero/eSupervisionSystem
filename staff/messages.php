<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
require '../classes/security.class.php';
require '../classes/userDetails.class.php';
require '../classes/communication.class.php';
require '../classes/errorList.class.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$staff_username = $currentStaff['staff_username'];

$c = new Communication ();
if ($_POST['communication_action']) {
    $el = new errorList ();

    try {
        $c->insert($staff_username);
    } catch (Exception $e) {

        $el->newList()->type('error')->message($e->getMessage())->go('messages.php?' . $_SERVER['QUERY_STRING']);
        exit;
    }

    $el->newList()->type('success')->message($c->getResponse())->go('messages.php?' . $_SERVER['QUERY_STRING']);
    exit;
}

// Determine which messages to display
if ($_GET['sid']) {
    // Filter sent messages by student username (sid)
    $c->getAll('message', $staff_username, 'staff', $_GET['sid']);

    // Get messages and count
    $sent = $c->getResponse();
    $message_count = count($sent);
} else {
    // Get sent messages from all students
    $message_count = -1;
}

// Get count of received messages
$c->received($staff_username, 'staff');
$received = $c->getResponse();
$received_count = count($received);

// Get allocated students
$u = new UserDetails ();
$u->GetAllocatedStudents($staff_username);
$students = $u->getResponse();

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Messages</title>
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
        }

        $(document).ready(function () {
            $(".button-collapse").sideNav();
            $('select').material_select();
        });

        // Client-side form validation
        // Function to display any error messages on form submit
        /**
         * @return {boolean}
         */
        function ValidateForm() {
            var isValid = true;

            // Validate student
            if (ValidateStudent(document.getElementById('communication_to_id').value) != '') isValid = false;

            // Validate message
            if (ValidateMessage(document.getElementById('communication_body').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the student
        function ValidateStudent(staff) {
            var output;
            if (/^\s*$/.test(staff)) {
                output = 'You must choose a student';
            } else {
                output = '';
            }

            document.getElementById('studentValidation').innerHTML = output;
            return output;
        }

        // Function to validate the message
        function ValidateMessage(type) {
            var output;
            if (/^\s*$/.test(type)) {
                output = 'You must enter a message';
            } else {
                output = '';
            }

            document.getElementById('messageValidation').innerHTML = output;
            return output;
        }
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
            if ($currentStaff['staff_authorised'] == 1) {
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
        <!-- MESSAGE SECTION START-->
 <div>
            <?php
                $el = new errorList ();
                if ($el->exists ()){
                    ?>
                     <p class='<?php echo $el->getType(); ?>'>
                    <?php echo $el->getResponse(); ?>
                </p>
                   <?

                }
            ?>
        </div>
        <!-- NEW MESSAGE SECTION START-->
        <div class="row" id="sendMessage">
            <div class="col s12">
                <div class="card">
                    <i class="small mdi-content-clear c_right-align"
                       onclick="toggleForm('#sendMessage', '#newMessage');"></i>

                    <div class="card-content">
                        <span class="card-title green-text">New Message</span>

                        <form id="communication" method="POST"
                              action="messages.php?<?php echo $_SERVER['QUERY_STRING']; ?>"
                              enctype="multipart/form-data">
                            <input type='hidden' name='communication_action' value='sendmessage'/>
                            <input type="hidden" name="communication_from_id" value="<?php echo $staff_username; ?>">
                            <input type='hidden' name='communication_type_id' value='2'/>

                            <div class="col s12">
                                <label for="communication_to_id">Student</label>
                                <select name="communication_to_id" id="communication_to_id"
                                        onkeyup="ValidateStudent(this.value);"
                                        onblur="ValidateStudent(this.value);">
                                    <option value="" disabled="disabled" selected="selected">Choose...</option>
                                    <?php foreach ($students as $stu) {
                                        echo '<option value="' . $stu['student_username'] . '"' . (($_GET['sid'] == $stu['student_username']) ? 'selected="selected"' : '') . '>' . $stu['student_first'] . ' ' . $stu['student_last'] . ' (' . $stu['student_username'] . ') </option>';
                                    } ?>
                                </select>
                                <span id="studentValidation" class="red-text text-light-3 validation-error"></span>
                            </div>
                            <div class="input-field col s12">
                                <label for="communication_body">Message</label>
                                <textarea class="materialize-textarea" name="communication_body"
                                          id="communication_body" onkeyup="ValidateMessage(this.value);"
                                          onblur="ValidateMessage(this.value);"></textarea>
                                <span id="messageValidation" class="red-text text-light-3 validation-error"></span>
                            </div>
                            <div class="file-field input-field col s12">
                                <div class="waves-effect waves-teal waves-light green btn-flat white-text">
                                    <span>File</span>
                                    <input type="file" name="fileToUpload" id="fileToUpload"/>
                                </div>
                            </div>
                            <div class="input-field col s12">
                                <button  type="submit" onclick="return ValidateForm();"
                                        class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- NEW MESSAGE SECTION END-->

        <div class="col s10 m12 offset-s1 card">
            <a onClick="toggleForm('#sendMessage', '#newMessage');" class="c_right_align" id="newMessage">
                <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">New Message
                </div>
            </a>

            <div class="card-content">
                <span class="card-title green-text">Message History</span>
                <?php if ($message_count > 0) {
                    // Get student name
                    $ud = new UserDetails ();
                    $ud->GetStudentDetails($_GET['sid']);
                    $student = $ud->getResponse();

                    echo '<p>There are ' . $message_count . ' messages between you and ' . $student[0]['student_first'] . ' ' . $student[0]['student_last'] . '.</p>';
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
                                    class="c_right-align waves-effect waves-teal waves-light blue btn-flat white-text">
                                Filter
                            </button>
                        </div>
                    </form>
                    <!-- STUDENT FILTER FORM END -->
                </div>

                <?php if ($message_count > 0) {
                    foreach ($sent as $s) { ?>
                        <ul class="collection">
                            <li class="collection-item" <?php echo ($s['communication_from_id'] == $staff_username) ? 'style="background-color: #fafafa;"' : '' ?> >
                                <form action="readfile.php" method="POST">
                                    <p>
                                        <span class="green-text">
                                            <b>
                                                <?php if ($s['communication_from_id'] == $staff_username) {
                                                    // Message is from current staff user
                                                    echo 'Me';
                                                } else {
                                                    // Message is from student
                                                    echo $s['student_first'] . " " . $s['student_last'];
                                                } ?>
                                            </b>
                                        </span>
                                        &#8212;

                                        <?php
                                        // Pretty format the date
                                        $date = strtotime($s['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($s['communication_time_added'], 0, -3); ?>
                                    </p>

                                    <p>
                                        <?php echo $s['communication_body']; ?>
                                    </p>

                                    <?php
                                    if ($s['communication_file_id'] > 0) { ?>
                                        <hr/>
                                        <p>
                                        <input type='hidden' name='file_id' value="<?php echo $s['communication_file_id']; ?>"/>
                                            <button 
                                                class="waves-effect waves-teal waves-light green btn-flat white-text"
                                                style="margin-bottom: 0; margin-top: 15px;">
                                                View File<i class="mdi-editor-attach-file right"></i></button>
                                        </p>
                                    <?php } ?>
                                </form>
                            </li>
                        </ul>
                    <?php }
                } else if ($message_count == 0) {
                    // No messages found for current student
                    echo '<ul class="collection"><li class="collection-item">No messages</li></ul>';
                } ?>
            </div>
        </div>
        <!--MESSAGING SECTION END-->
    </div>
</div>
<!-- end container -->
</body>
<script>
$('communication').submit(function(){
    $('button').remove ();
});
</script>
</html>