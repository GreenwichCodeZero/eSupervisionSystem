<?php

// Initialise session
session_start();

error_reporting(0);

require '../database-connection.php';
require '../validation.php';
require '../login-check.php';
require '../classes/userDetails.class.php';
require '../classes/meetings.class.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

// Globals
$errorList = array();
$outputText = $errorListOutput = '';
$currentStaff = $_SESSION['currentUser'];
$currentStaff = $_SESSION['currentUser'];

$staff_id = $currentStaff['staff_id'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
}

$emailHeaders = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
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
        $title = mysqli_real_escape_string($link, stripslashes(strip_tags($_POST['title'])));
        if (preg_match('/^\s*$/', $title)) {
            array_push($errorList, 'Title is required');
        }

        // Validate meeting type
        $type = mysqli_real_escape_string($link, stripslashes($_POST['type']));
        if (!preg_match('/^[0-9]{1}$/', $type)) {
            array_push($errorList, 'Type not valid');
        }

        // Validate meeting message
        $content = mysqli_real_escape_string($link, stripslashes(strip_tags($_POST['content'])));
        if (preg_match('/^\s*$/', $content)) {
            array_push($errorList, 'Message is required');
        }

        // Validate meeting timeslot
        $selectedTimeslot = mysqli_real_escape_string($link, stripslashes($_POST['timeslot']));
        $timeslotId = $meetingDate = null;
        if (!preg_match('/^[0-9]{1,},[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $selectedTimeslot)) {
            array_push($errorList, 'Time slot not valid');
        } else {
            // Get timeslot ID and meeting date
            $timeslotId = explode(',', $selectedTimeslot)[0];
            $meetingDate = explode(',', $selectedTimeslot)[1];
        }

        // Validate student username
        $meetingToStudentUsername = mysqli_real_escape_string($link, stripslashes($_POST['student']));
        if (!preg_match('/^([A-Za-z]{2})([0-9]{2,3})$/', $meetingToStudentUsername)) {
            array_push($errorList, 'Student not valid');
        }

        // Check for and display any errors
        if (count($errorList) > 0) {
            $errorListOutput = DisplayErrorMessages($errorList);
        } else {
            // No errors
            // Insert into database
            if (InsertMeeting($link, $timeslotId, $meetingDate, $title, $content, $type, $meetingToStudentUsername, $currentStaff['staff_username'])) {
                // Send email to student
                mail(
                    $meetingToStudentUsername . '@greenwich.ac.uk',
                    'New Meeting Request Received',
                    'A new meeting request was submitted and is waiting for you on the eSupervision System.',
                    $emailHeaders
                );

                $outputText = '<p class="success">Meeting saved.</p>';
            } else {
                $outputText = '<p class="error">Database error.</p>';
            }
        }
    } elseif (isset($_POST['recordMeeting'])) {
        // 'Record Meeting' button pressed
        // Server-side validation
        // Validate meeting message
        $contentRecord = mysqli_real_escape_string($link, stripslashes(strip_tags($_POST['contentRecord'])));
        if (preg_match('/^\s*$/', $contentRecord)) {
            array_push($errorList, 'Meeting record message is required');
        }

        // Get meeting ID and new status from GET
        $meetingId = mysqli_real_escape_string($link, stripslashes($_POST['meeting']));

        // Check for and display any errors
        if (count($errorList) > 0) {
            $errorListOutput = DisplayErrorMessages($errorList);
        } else {
            // No errors
            // Insert record into database
            if (UpdateRecordMeeting($link, $meetingId, $contentRecord)) {
                // Saved
                $outputText = '<p class="success">Meeting recorded.</p>';
            } else {
                $outputText = '<p class="error">Database error.</p>';
            }
        }
    } elseif (isset($_GET['meeting']) && isset($_GET['status'])) {
        // Change meeting status
        // Server-side validation
        // Get meeting ID and new status from GET
        $meetingId = mysqli_real_escape_string($link, stripslashes($_GET['meeting']));
        $newStatus = mysqli_real_escape_string($link, stripslashes($_GET['status']));

        // Update meeting status
        $studentUsername = UpdateMeetingStatus($link, $meetingId, $newStatus);

        // Send email to student
        mail(
            $studentUsername . '@greenwich.ac.uk',
            'Meeting Request Updated',
            'The status of a meeting request has been updated and is waiting for you on the eSupervision System.',
            $emailHeaders
        );

        $outputText = '<p class="success">Meeting status updated. Optionally record a message below.</p>';
    }

    // Create HTML option list of timeslots
    $timeslots = GetStaffTimeslots($link, $currentStaff['staff_username']);
    if ((count($timeslots)) > 0) {
        // Staff has timeslots
        $timeslotsOptionList = '<option value="" disabled="disabled" selected="selected">Choose...</option>';
        $timeslotsOptionListWeek0 = $timeslotsOptionListWeek1 = $timeslotsOptionListWeek2 = '';

        // Get current week number
        $currentWeekNumber = date('W');

        // Loop over how many weeks should be displayed. Default is 2
        for ($i = 0; $i < 2; $i++) {
            // Loop over each individual timeslot
            foreach ($timeslots as $timeslot) {

                // Initialise value with timeslot ID
                $timeslotValue = $timeslot['timeslot_id'] . ',';

                switch ($timeslot['timeslot_day']) {
                    case 'M':
                        // Get date of the Monday specified i weeks away
                        $timeslotDateTime = strtotime("This Monday + $i Weeks");

                        break;
                    case 'TU':
                        // Get date of the Tuesday specified i weeks away
                        $timeslotDateTime = strtotime("This Tuesday + $i Weeks");

                        break;
                    case 'W':
                        // Get date of the Wednesday specified i weeks away
                        $timeslotDateTime = strtotime("This Wednesday + $i Weeks");

                        break;
                    case 'TH':
                        // Get date of the Thursday specified i weeks away
                        $timeslotDateTime = strtotime("This Thursday + $i Weeks");

                        break;
                    case 'F':
                        // Get date of the Friday specified i weeks away
                        $timeslotDateTime = strtotime("This Friday + $i Weeks");

                        break;
                }

                // Check timeslot is available
                if (CheckTimeslotAvailability($link, $timeslot['timeslot_id'], date('Y-m-d', $timeslotDateTime))) {
                    // Timeslot is available
                    // Add date to the value
                    $timeslotValue .= date('Y-m-d', $timeslotDateTime); // 2015-12-31

                    // Pretty format the date
                    $timeslotDisplay = date('l j M', $timeslotDateTime); // Thursday 31 Dec

                    // Pretty format the time
                    $timeStart = gmdate('H:i', floor($timeslot['timeslot_time'] * 3600)); // HH:MM
                    $timeEnd = gmdate('H:i', floor(($timeslot['timeslot_time'] + 0.5) * 3600)); // HH:MM

                    // Add date and time
                    $timeslotDisplay .= ', ' . $timeStart . '-' . $timeEnd;

                    // Output timeslot as drop down item
                    switch (date('W', $timeslotDateTime)) {
                        case $currentWeekNumber:
                            // Current week
                            $timeslotsOptionListWeek0 .= '<option value="' . $timeslotValue . '">' . $timeslotDisplay . '</option>';

                            break;
                        case ($currentWeekNumber + 1):
                            // Next week
                            $timeslotsOptionListWeek1 .= '<option value="' . $timeslotValue . '">' . $timeslotDisplay . '</option>';

                            break;
                        case ($currentWeekNumber + 2):
                            // Week after next week
                            $timeslotsOptionListWeek2 .= '<option value="' . $timeslotValue . '">' . $timeslotDisplay . '</option>';

                            break;
                    }
                }
            }
        }

        // Add weeks to list
        $timeslotsOptionList .= $timeslotsOptionListWeek0 . $timeslotsOptionListWeek1 . $timeslotsOptionListWeek2;
    } else {
        // Staff has no timeslots
        $timeslotsOptionList = '<option value="" disabled="disabled" selected="selected">None available</option>';
    }

    // Get allocated students
    $u = new UserDetails ();
    $u->GetAllocatedStudents($currentStaff['staff_username']);
    $students = $u->getResponse();

    // Create HTML option list of students
    if ((count($students)) > 0) {
        // Staff has students allocated to them
        $studentsOptionList = '<option value="" disabled="disabled" selected="selected">Choose...</option>';
        foreach ($students as $student) {
            $studentsOptionList .= '<option value="' . $student['student_username'] . '">' . $student['student_first'] . ' ' . $student['student_last'] . ' (' . $student['student_username'] . ')</option>';
        }
    } else {
        // Staff has no students allocated to them
        $studentsOptionList = '<option value="" disabled="disabled" selected="selected">No students allocated</option>';
    }

    // Get all staff's meetings
    $m = new Meeting ();
    $m->getAll($currentStaff['staff_username']);
    $meetings = $m->getResponse();
    $meeting_count = count($meetings);
}

// Check for and display any errors
if (count($errorList) > 0) {
    $errorListOutput = DisplayErrorMessages($errorList);
}

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach ($getStaffDetails as $staffDetail) {
    $staffAuthorised = $staffDetail['staff_authorised'];
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Meetings</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
        function ValidateRequestForm() {
            var isValid = true;

            // Validate meeting title
            if (ValidateTitle(document.getElementById('title').value) != '') isValid = false;

            // Validate meeting message
            if (ValidateContentRequest(document.getElementById('content').value) != '') isValid = false;

            // Validate meeting timeslot
            if (ValidateTimeslot(document.getElementById('timeslot').value) != '') isValid = false;

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
        function ValidateContentRequest(content) {
            var output;
            if (/^\s*$/.test(content)) {
                output = 'Message is required';
            } else {
                output = '';
            }

            document.getElementById('contentValidation').innerHTML = output;
            return output;
        }

        // Function to validate the meeting timeslot
        function ValidateTimeslot(timeslot) {
            var output;
            if (!/^[0-9]{1,},[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(timeslot)) {
                output = 'Time slot is required';
            } else {
                output = '';
            }

            document.getElementById('timeslotValidation').innerHTML = output;
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

        // Client-side form validation
        // Function to display any error messages on form submit
        /**
         * @return {boolean}
         */
        function ValidateRecordForm() {
            var isValid = true;

            // Validate meeting message
            if (ValidateContentRecord(document.getElementById('contentRecord').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the meeting message
        function ValidateContentRecord(content) {
            var output;
            if (/^\s*$/.test(content)) {
                output = 'Message is required';
            } else {
                output = '';
            }

            document.getElementById('contentRecordValidation').innerHTML = output;
            return output;
        }
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
            if ($getStaffDetails[0]['staff_authorised'] == 1) {
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

    <!-- Output message text -->
    <?php echo $outputText; ?>

    <div class="red-text text-light-3 validation-error">
        <?php echo $errorListOutput; ?>
    </div>

    <!-- MEETING RECORD DETAILS SECTION START-->
    <?php if (isset($_GET['meeting']) && isset($_GET['status'])) {
        // Get meeting details
        $m->getSingle($meetingId);
        $updatedmeeting = $m->getResponse();
        ?>

        <div class="row" id="recordMeeting">
            <div class="col s12">
                <div class="card">
                    <i class="small mdi-content-clear c_right-align"
                       onclick="toggleForm('#recordMeeting', null);"></i>

                    <div class="card-content">
                        <span class="card-title green-text">Record Meeting Details</span>

                        <p>
                            <b>Title:</b> <?php echo $updatedmeeting[0]['meeting_title']; ?>
                        </p>

                        <p>
                            <?php
                            // Pretty format the date
                            $date = strtotime($updatedmeeting[0]['meeting_date']);
                            $prettyDate = date('l j F Y', $date);

                            // Pretty format the time
                            $timeStart = gmdate('H:i', floor($updatedmeeting[0]['meeting_time'] * 3600)); // HH:MM
                            $timeEnd = gmdate('H:i', floor(($updatedmeeting[0]['meeting_time'] + 0.5) * 3600)); // HH:MM
                            $prettyTime = $timeStart . '-' . $timeEnd;

                            // Output date and time
                            echo '<b>Date:</b> ' . $prettyDate . ', ' . $prettyTime; ?>
                        </p>

                        <p>
                            <b>Student:</b> <?php echo $updatedmeeting[0]['student_first'] . ' ' . $updatedmeeting[0]['student_last']; ?>
                        </p>

                        <p>
                            <b>Message:</b> <?php echo $updatedmeeting[0]['meeting_content']; ?>
                        </p>

                        <p class="grey-text text-darken-1" style="font-size: 0.8em">
                            <b>Type:</b> <?php echo $updatedmeeting[0]['meeting_type']; ?>.
                            <b>Current status:</b> <?php echo $updatedmeeting[0]['meeting_status']; ?>
                        </p>

                        <hr/>

                        <form name="meetingRecord" method="post" action="meetings.php">
                            <div class="input-field col s12">
                                <label for="contentRecord">Message</label>
                                                <textarea id="contentRecord" name='contentRecord'
                                                          class="materialize-textarea"
                                                          onkeyup="ValidateContentRecord(this.value);"
                                                          onblur="ValidateContentRecord(this.value);"><?php echo $updatedmeeting[0]['meeting_status_content']; ?></textarea>
                                <span id="contentRecordValidation"
                                      class="red-text text-light-3 validation-error"></span>
                            </div>

                            <div class="input-field col s12">
                                <input type="hidden" name="meeting"
                                       value="<?php echo $updatedmeeting[0]['meeting_id']; ?>">

                                <button type="submit" name="recordMeeting" onclick="return ValidateRecordForm();"
                                        class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                                    Submit
                                </button>

                                <a href="meetings.php" title="Cancel"
                                   class="c_right-align waves-effect waves-teal waves-light btn-flat">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>
    <!-- MEETING RECORD DETAILS SECTION END-->

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
                                  onkeyup="ValidateContentRequest(this.value);"
                                  onblur="ValidateContentRequest(this.value);"></textarea>
                            <span id="contentValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <div class="col s6">
                            <label for="timeslot">Time slot</label>
                            <select id="timeslot" name="timeslot" onkeyup="ValidateTimeslot(this.value);"
                                    onblur="ValidateTimeslot(this.value);">
                                <?php echo $timeslotsOptionList; ?>
                            </select>
                            <span id="timeslotValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <div class="col s6">
                            <label for="student">Student</label>
                            <select id="student" name="student" onkeyup="ValidateStudent(this.value);"
                                    onblur="ValidateStudent(this.value);">
                                <?php echo $studentsOptionList; ?>
                            </select>
                            <span id="studentValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

                        <div class="input-field col s12">
                            <button type="submit" name="requestMeeting" onclick="return ValidateRequestForm();"
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

                    <p>You have <?php echo $meeting_count; ?> meeting records.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MEETING CARDS START -->
    <?php if ($meeting_count > 0) { ?>

        <div class="row">

            <?php foreach ($meetings as $meeting) { ?>

                <div class="col s12">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title green-text">
                                <?php echo $meeting['meeting_title']; ?>
                            </span>

                            <p>
                                <b><?php
                                    // Pretty format the date
                                    $date = strtotime($meeting['meeting_date']);
                                    $prettyDate = date('l j F Y', $date);

                                    // Pretty format the time
                                    $timeStart = gmdate('H:i', floor($meeting['meeting_time'] * 3600)); // HH:MM
                                    $timeEnd = gmdate('H:i', floor(($meeting['meeting_time'] + 0.5) * 3600)); // HH:MM
                                    $prettyTime = $timeStart . '-' . $timeEnd;

                                    // Output date and time
                                    echo $prettyDate . ', ' . $prettyTime; ?></b>
                            </p>

                            <p>
                                Requested by <?php echo $meeting['student_first'] . ' ' . $meeting['student_last']; ?>
                            </p>

                            <hr/>

                            <p>
                                <b>Request message:</b> <?php echo $meeting['meeting_content']; ?>
                            </p>

                            <?php if ($meeting['meeting_status_content'] != '') { ?>
                                <p>
                                    <b>Status message:</b> <?php echo $meeting['meeting_status_content']; ?>
                                </p>
                            <?php } ?>

                            <p class="grey-text text-darken-1" style="font-size: 0.8em">
                                <b>Type:</b> <?php echo $meeting['meeting_type']; ?>.
                                <b>Current status:</b> <?php echo $meeting['meeting_status']; ?>

                            </p>
                        </div>
                        <div class="card-action">

                            <?php
                            if ($meeting['meeting_status'] == "Pending") {
                                echo '
                                <a href="meetings.php?meeting=' . $meeting['meeting_id'] . '&status=2"
                               title="Accept">Accept</a>
                            <a href="meetings.php?meeting=' . $meeting['meeting_id'] . '&status=3"
                               title="Decline">Decline</a>';
                            }  else if ($meeting['meeting_status'] == "Accepted") {
                                echo '<a href="meetings.php?meeting=' . $meeting['meeting_id'] . '&status=3"
                               title="Decline">Decline</a>
                               <a href="meetings.php?meeting=' . $meeting['meeting_id'] . '&status=4"
                               title="Held">Held</a>';
                            }

                            ?>

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

</html>