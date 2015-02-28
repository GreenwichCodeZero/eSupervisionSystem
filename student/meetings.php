<?php

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
$currentStudent = $_SESSION['currentUser'];

// Determine permissions of current user
if ($currentStudent['user_type'] === 'staff') {
    // Redirect to staff dashboard
    header('Location: /codezero/staff/index.php');
}

$emailHeaders = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

// Get student supervisor
$u = new UserDetails ();
$u->studentSuper($currentStudent['student_id']);
$supervisor = $u->getResponse();

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

        // Check for and display any errors
        if (count($errorList) > 0) {
            $errorListOutput = DisplayErrorMessages($errorList);
        } else {
            // No errors
            // Insert into database
            if (InsertMeeting($link, $timeslotId, $meetingDate, $title, $content, $type, $currentStudent['student_username'], $supervisor[0]['staff_username'])) {
                // Send email to supervisor
                mail(
                    $supervisor[0]['staff_username'] . '@greenwich.ac.uk',
                    'New Meeting Request Received',
                    'A new meeting request was submitted and is waiting for you on the eSupervision System.',
                    $emailHeaders
                );

                $outputText = '<p class="success">Meeting saved.</p>';
            } else {
                $outputText = '<p class="error">Database error.</p>';
            }
        }
    }

    // Create HTML option list of timeslots
    $timeslots = GetStaffTimeslots($link, $supervisor[0]['staff_username']);
    if ((count($timeslots)) > 0) {
        // Staff has timeslots
        $timeslotsOptionList = '<option value="" disabled="disabled" selected="selected">Choose...</option>';

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
                $timeslotsOptionList .= '<option value="' . $timeslotValue . '">' . $timeslotDisplay . '</option>';
            }
        }
    } else {
        // Staff has no timeslots
        $timeslotsOptionList = '<option value="" disabled="disabled" selected="selected">None available</option>';
    }

    // Get all students meetings
    $m = new Meeting ();
    $m->getAll($currentStudent['student_username']);
    $meetings = $m->getResponse();
    $meeting_count = count($meetings);
}

// Check for and display any errors
if (count($errorList) > 0) {
    $errorListOutput = DisplayErrorMessages($errorList);
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
        function ValidateForm() {
            var isValid = true;

            // Validate meeting title
            if (ValidateTitle(document.getElementById('title').value) != '') isValid = false;

            // Validate meeting message
            if (ValidateContent(document.getElementById('content').value) != '') isValid = false;

            // Validate meeting timeslot
            if (ValidateTimeslot(document.getElementById('timeslot').value) != '') isValid = false;

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
			<li>
                <a href="../logout.php" title="Logout">Logout</a>
            </li>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>

<div class="container">

    <!-- Output message text -->
    <div
        class="red-text text-light-3 validation-error"><?php echo $outputText; ?><?php echo $errorListOutput; ?></div>

    <!-- MEETING REQUEST SECTION START-->
    <div class="row" id="sendMessage">
        <div class="col s12">
            <div class="card">
                <i class="small mdi-content-clear c_right-align"
                   onclick="toggleForm('#sendMessage', '#newMessage');"></i>

                <div class="card-content">
                    <span class="card-title green-text">Request Meeting</span>

                    <p>Request a meeting
                        with <?php echo $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last']; ?>.</p>

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
                            <label for="timeslot">Time slot</label>
                            <select id="timeslot" name="timeslot" onkeyup="ValidateTimeslot(this.value);"
                                    onblur="ValidateTimeslot(this.value);">
                                <?php echo $timeslotsOptionList; ?>
                            </select>
                            <span id="timeslotValidation" class="red-text text-light-3 validation-error"></span>
                        </div>

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

                    <p>You have <?php echo $meeting_count; ?> meeting records.</p>

                    <ul class="collection">
                        <?php foreach ($meetings as $meeting) { ?>
                            <li class="collection-item">
                                <p><span class="green-text"><b><?php echo $meeting['meeting_title']; ?></b></span>
                                    &#8212;

                                    <?php
                                    // Pretty format the date
                                    $date = strtotime($meeting['meeting_date']);
                                    $prettyDate = date('l j F Y', $date);

                                    // Pretty format the time
                                    $timeStart = gmdate('H:i', floor($meeting['meeting_time'] * 3600)); // HH:MM
                                    $timeEnd = gmdate('H:i', floor(($meeting['meeting_time'] + 0.5) * 3600)); // HH:MM
                                    $prettyTime = $timeStart . '-' . $timeEnd;

                                    // Output date and time
                                    echo $prettyDate . ', ' . $prettyTime; ?>
                                </p>

                                <p>
                                    <?php echo $meeting['meeting_content']; ?>
                                </p>

                                <?php if ($meeting['meeting_status_content'] != '') { ?>
                                    <p style="font-size: 0.9em">
                                        <b>Message from
                                            supervisor:</b> <?php echo $meeting['meeting_status_content']; ?>
                                    </p>
                                <?php } ?>

                                <p class="grey-text text-darken-1" style="font-size: 0.8em">
                                    <b>Type:</b> <?php echo $meeting['meeting_type']; ?>. <b>Current
                                        status:</b> <?php echo $meeting['meeting_status']; ?></p>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- VIEW ALL MEETINGS END -->

</div>
</body>

</html>