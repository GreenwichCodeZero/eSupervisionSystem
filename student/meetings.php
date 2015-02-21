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
$currentStudent = $_SESSION['currentUser'];

// Determine permissions of current user
if ($currentStudent['user_type'] === 'staff') {
    // Redirect to staff dashboard
    header('Location: /codezero/staff/index.php');
}

// Get student supervisor
$u = new UserDetails ();
$u->studentSuper($currentStudent['student_id']);
$supervisor = $u->getResponse();

if (isset($_POST['requestMeeting'])) {
    // 'Create Meeting' button pressed
    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        $outputText .= '<p class="error">Error connecting to database, please try again.</p>';
    } else {
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

        $meeting_from_id = $currentStudent['student_username'];
        $meeting_to_id = $supervisor[0]['staff_username'];

        // Insert into database
        if (InsertMeeting($link, $date, $title, $content, $type, $meeting_from_id, $meeting_to_id)) {
            // Send email to supervisor
            //todo remove tm112 after testing
            $headers = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
                'MIME-Version: 1.0' . "\r\n" .
                'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            mail(
                'tm112@greenwich.ac.uk, ' . $supervisor[0]['staff_username'] . '@greenwich.ac.uk',
                'New Meeting Request Received',
                'A new meeting request was submitted and is waiting for you on the eSupervision System.',
                $headers
            );

            $outputText = '<p class="success">Meeting saved.</p>';
        } else {
            $outputText = '<p class="error">Database error.</p>';
        }
    }
}

$m = new Meeting ();
$m->getAll(null, $currentStudent['student_username']);
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

                        <div class="col s12">
                            <label for="date">Date</label>
                            <input id="date" name="date" type="date" class="datepicker"
                                   onkeyup="ValidateDate(this.value);"
                                   onblur="ValidateDate(this.value);">
                            <span id="dateValidation" class="red-text text-light-3 validation-error"></span>
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

                    <p>You have <?php echo $meeting_count; ?> meeting records.</p>

                    <!-- Output message text -->
                    <?php echo $outputText; ?>

                    <ul class="collection">
                        <?php foreach ($meetings as $meeting) { ?>
                            <li class="collection-item">
                                <p><span class="green-text"><b><?php echo $meeting['meeting_title']; ?></b></span>
                                    &#8212; <?php echo $meeting['meeting_date']; ?></p>

                                <p><?php echo $meeting['meeting_content']; ?></p>

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
<script>
    $(document).ready(function () {
        $('.modal-trigger').leanModal();
    });
</script>