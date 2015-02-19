<?php
//todo file upload?
//todo date format?

// Initialise session
session_start();

require '../database-connection.php';
require '../validation.php';
require '../login-check.php';
require '../classes/userDetails.class.php';
require '../classes/meetings.class.php';

// Globals
$errorList = array();
$outputText = $errorListOutput = '';
$currentStaff = $_SESSION['currentUser'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
}

$headers = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

// Create database connection
if (!($link = GetConnection())) {
    // Database connection error occurred
    $outputText .= '<p class="error">Error connecting to database, please try again.</p>';
} else {
    if (isset($_POST['requestMeeting'])) {
        // 'Create Meeting' button pressed
        // Server-side validation
        // Validate meeting title
        $title = mysqli_real_escape_string($link, stripslashes($_POST['title']));
        if (preg_match('/^\s*$/', $title)) {
            array_push($errorList, 'Title is required');
        }

        // Validate meeting type
        $type = mysqli_real_escape_string($link, stripslashes($_POST['type']));
        if (!preg_match('/^[0-9]{1}$/', $type)) {
            array_push($errorList, 'Type not valid');
        }

        // Validate meeting message
        $content = mysqli_real_escape_string($link, stripslashes($_POST['content']));
        if (preg_match('/^\s*$/', $content)) {
            array_push($errorList, 'Message is required');
        }

        // Validate meeting date
        $date = mysqli_real_escape_string($link, stripslashes($_POST['date']));
        if (preg_match('/^\s*$/', $date)) {
            array_push($errorList, 'Date not valid');
            //todo adjust regex to suit required date format
        }

        $meeting_from_id = $currentStaff['staff_username'];

        // Validate student username
        $meeting_to_id = mysqli_real_escape_string($link, stripslashes($_POST['student']));
        if (!preg_match('/^([A-Za-z]{2})([0-9]{2,3})$/', $meeting_to_id)) {
            array_push($errorList, 'Student not valid');
        }

        // Insert into database
        if (InsertMeeting($link, $date, $title, $content, $type, $meeting_to_id, $meeting_from_id)) {
            // Send email to supervisor
            //todo remove tm112 after testing
            mail(
                'tm112@greenwich.ac.uk, ' . $meeting_to_id . '@greenwich.ac.uk',
                'New Meeting Request Received',
                'A new meeting request was submitted and is waiting for you on the eSupervision System.',
                $headers
            );

            $outputText = '<p class="success">Meeting saved.</p>';
        } else {
            $outputText = '<p class="error">Database error.</p>';
        }
    } elseif (isset($_GET['meeting']) && isset($_GET['status'])) {
        // Change meeting status
        // Get meeting ID and new status from GET
        //todo clean
        $meetingId = $_GET['meeting'];
        $newStatus = $_GET['status'];

        // Update meeting status
        $studentUsername = UpdateMeetingStatus($link, $meetingId, $newStatus);

        // Send email to student
        //todo remove tm112 after testing
        mail(
            'tm112@greenwich.ac.uk, ' . $studentUsername . '@greenwich.ac.uk',
            'Meeting Request Updated',
            'The status of a meeting request has been updated and is waiting for you on the eSupervision System.',
            $headers
        );

        $outputText = '<p class="success">Meeting status updated.</p>';
    }
}

// Get students
$u = new UserDetails ();
$u->AllMyStudents($currentStaff['staff_username']);
$students = $u->getResponse();

// Create HTML option list of students
if ((count($students)) > 0) {
    // Staff has studetns allocated to them
    $studentsOptionList = '<option value="" disabled="disabled" selected="selected">Choose...</option>';
    foreach ($students as $student) {
        $studentsOptionList .= '<option value="' . $student['student_username'] . '">' . $student['student_first'] . ' ' . $student['student_last'] . '</option>';
    }
} else {
    // Staff has no students allocated to them
    $studentsOptionList = '<option value="" disabled="disabled" selected="selected">No students allocated</option>';
}

$m = new Meeting ();
$m->getAll(null, $currentStaff['staff_username']);
$meetings = $m->getResponse();
$meeting_count = count($meetings);

// Check for and display any errors
if (count($errorList) > 0) {
    $errorListOutput = DisplayErrorMessages($errorList);
}

?>

<head>
    <title>Meetings</title>
    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript">
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

            // Validate meeting title
            if (ValidateTitle(document.getElementById('title').value) != '') isValid = false;

            // Validate meeting message
            if (ValidateContent(document.getElementById('content').value) != '') isValid = false;

            // Validate meeting date
            if (ValidateDate(document.getElementById('date').value) != '') isValid = false;

            // Validate the selected student
            if (ValidateStudent(document.getElementById('student').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the meeting title
        function ValidateTitle(title) {
            var output;
            if (/^\s*$/.test(title)) {
                output = 'Title is required';
            } else {
                output = '';
            }

            document.getElementById('titleValidation').innerHTML = output;
            return output;
        }

        // Function to validate the meeting message
        function ValidateContent(content) {
            var output;
            if (/^\s*$/.test(content)) {
                output = 'Message is required';
            } else {
                output = '';
            }

            document.getElementById('contentValidation').innerHTML = output;
            return output;
        }

        // Function to validate the meeting date
        function ValidateDate(date) {
            //todo adjust regex to suit required date format

            var output;
            if (/^\s*$/.test(date)) {
                output = 'Date is required';
            } else {
                output = '';
            }

            document.getElementById('dateValidation').innerHTML = output;
            return output;
        }

        // Function to validate the selected student
        function ValidateStudent(student) {
            var output;
            if (/^\s*$/.test(student)) {
                output = 'You must choose a student';
            } else {
                output = '';
            }

            document.getElementById('studentValidation').innerHTML = output;
            return output;
        }
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
                <a href="uploads.php">Uploads</a>
            </li>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>

<div class="container">
    <!-- MEETING REQUEST SECTION START-->
    <div class="row" id="sendMessage">
        <div class="col s12">
            <div class="card">
                <i class="small mdi-content-clear c_right-align"
                   onclick="toggleForm('#sendMessage', '#newMessage');"></i>

                <div class="card-content">
                    <span class="card-title green-text">Request Meeting</span>

                    <form name="meeting" method="post" action="meetings.php">
                        <div class="input-field col s6">
                            <label for="title">Title</label>
                            <input id="title" name="title" type="text" onkeyup="ValidateTitle(this.value);"
                                   onblur="ValidateTitle(this.value);"/>
                            <span id="titleValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <div class="col s6">
                            <label for="type">Type</label>
                            <select id="type" name="type">
                                <option value="1">Virtual</option>
                                <option value="2">Face to Face</option>
                            </select>
                        </div>

                        <div class="input-field col s12">
                            <label for="content">Message</label>
                        <textarea id="content" name='content' class="materialize-textarea"
                                  onkeyup="ValidateContent(this.value);"
                                  onblur="ValidateContent(this.value);"></textarea>
                            <span id="contentValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <div class="col s6">
                            <label for="date">Date</label>
                            <input id="date" name="date" type="date" class="datepicker"
                                   onkeyup="ValidateDate(this.value);"
                                   onblur="ValidateDate(this.value);">
                            <span id="dateValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <div class="col s6">
                            <label for="student">Student</label>
                            <select id="student" name="student" onkeyup="ValidateStudent(this.value);"
                                    onblur="ValidateStudent(this.value);">
                                <?php echo $studentsOptionList; ?>
                            </select>
                            <span id="studentValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <!--<input type="file" name="fileToUpload" id="fileToUpload">-->

                        <div class="input-field col s12">
                            <button type="submit" name="requestMeeting" onclick="return ValidateForm();"
                                    class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- MEETING REQUEST SECTION END-->

    <!-- VIEW ALL MEETINGS START -->
    <div class="row">
        <div class="col s12">
            <div class="card">
                <a onclick="toggleForm('#sendMessage', '#newMessage');" class="c_right_align" id="newMessage">
                    <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Request
                        Meeting
                    </div>
                </a>

                <div class="card-content">
                    <span class="card-title green-text">Meeting History</span>

                    <p>You have <?php echo $meeting_count; ?> meeting records.</p><!-- todo -->

                    <!-- Output message text -->
                    <?php echo $outputText; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MEETING CARDS START -->
    <?php if ($meeting_count > 0) { ?>

        <div class="row">

            <?php foreach ($meetings as $meeting) { ?>

                <div class="col m12 l6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title green-text">
                                <?php echo $meeting['meeting_title']; ?>
                            </span>

                            <p>
                                <b><?php echo $meeting['meeting_date']; ?></b>
                            </p>

                            <p>
                                Requested by <?php echo $meeting['student_first'] . ' ' . $meeting['student_last']; ?>.
                            </p>

                            <hr/>

                            <p>
                                <b>Message:</b> <?php echo $meeting['meeting_content']; ?>
                            </p>

                            <p class="grey-text text-darken-1" style="font-size: 0.8em">
                                <b>Type:</b> <?php echo $meeting['meeting_type']; ?>.
                                <b>Current status:</b> <?php echo $meeting['meeting_status']; ?>
                            </p>

                            <p>
                                <?php echo $userDetails; ?>
                            </p>
                        </div>
                        <div class="card-action">
                            <!-- todo actions -->
                            <a href="meetings.php?meeting=<?php echo $meeting['meeting_id']; ?>&status=2"
                               title="Accept">Accept</a>
                            <a href="meetings.php?meeting=<?php echo $meeting['meeting_id']; ?>&status=3"
                               title="Decline">Decline</a>
                            <!--<a href="#" title="Record">Record Details</a>todo-->
                        </div>
                    </div>
                </div>

            <?php } ?>

        </div>

    <?php } ?>
    <!-- MEETING CARDS END -->

    <!-- VIEW ALL MEETINGS END -->

</div>
</body>
<script>
    $(document).ready(function () {
        $('.modal-trigger').leanModal();
    });
</script>