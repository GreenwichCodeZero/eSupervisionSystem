<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
require '../classes/security.class.php';
require '../classes/userDetails.class.php';
require '../database-connection.php';
require '../validation.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$selectedStudents = $_POST['students'];


$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();


// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
} else if ($currentStaff['staff_authorised'] !== '1') {
    // Do not allow access to unauthorised staff
    header('Location: index.php');
}

$userDetails = new UserDetails ();

$emailHeaders = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$userDetails = new UserDetails ();

if (isset($_POST['saveAllocate'])) {
    // Allocate button has been pressed
    // Get data
    $type = $_POST['type'];
    $allocated_staff_id = $_POST['staff'];
    $allocatedStudents = $_SESSION['allocatedStudents'];

    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        $outputText .= 'Error connecting to database, please try again.';
    } else {
        // Server-side validation
        // Validate allocated staff ID
        $allocated_staff_id = mysqli_real_escape_string($link, stripslashes(strip_tags($allocated_staff_id)));
        if (!preg_match('/^[0-9]{1,}$/', $allocated_staff_id)) {
            array_push($errorList, 'Supervisor ID is required');
        }

        // Validate current staff ID
        $staff_id = mysqli_real_escape_string($link, stripslashes(strip_tags($staff_id)));
        if (!preg_match('/^[0-9]{1,}$/', $staff_id)) {
            array_push($errorList, 'Current staff ID is required');
        }

        // Arrays of students being allocated to new staff
        $newSupervisorStudentUsernames = $oldSupervisorStudentUsernames = $newSecondMarkerStudentUsernames = $oldSecondMarkerStudentUsernames = $studentClashes = array();

        // Loop over each allocated student
        foreach ($allocatedStudents as $allocatedStudent) {
            // Get student ID
            $userDetails->GetStudentDetails($allocatedStudent);
            $studentDetails = $userDetails->getResponse()[0];
            $studentUserId = $studentDetails['student_id'];

            // Server-side validation
            // Validate student ID
            $studentUserId = mysqli_real_escape_string($link, stripslashes(strip_tags($studentUserId)));
            if (!preg_match('/^[0-9]{1,}$/', $studentUserId)) {
                array_push($errorList, 'Student ID is required');
            }

            // Check for and display any errors
            if (count($errorList) > 0) {
                $outputText = DisplayErrorMessages($errorList);
            } else {
                // No errors
                switch ($type) {
                    case 'supervisor':
                        // Send email to old supervisor
                        // Get old supervisor username
                        $userDetails->getStudentSupervisor($studentDetails['student_id']);
                        $studentOldSupervisorDetails = $userDetails->getResponse()[0];
                        $studentOldSupervisorUsername = $studentOldSupervisorDetails['staff_username'];
                        $oldSupervisorStudentUsernames[$studentOldSupervisorUsername][] = $studentDetails;

                        if ($studentOldSupervisorDetails['staff_id'] == $allocated_staff_id) {
                            // Selected student is already allocated selected supervisor
                            $studentClashes[] = $studentDetails['student_first'] . ' ' . $studentDetails['student_last'];
                        } else {
                            // Insert into database
                            if (AllocateStudentSupervisor($link, $studentUserId, $allocated_staff_id, $staff_id)) {
                                // Send email to student
                                mail(
                                    $studentDetails['student_username'] . '@greenwich.ac.uk',
                                    'Supervisor Allocation Changed',
                                    'You have been allocated to a new supervisor, check the eSupervision System for details.',
                                    $emailHeaders
                                );

                                // Send email to new supervisor
                                $newSupervisorStudentUsernames[] = $studentDetails;
                            } else {
                                $outputText = 'Database error.';
                            }
                        }

                        break;
                    case 'second':
                        // Send email to old second marker
                        // Get old second marker username
                        $userDetails->getStudentSecondMarker($studentDetails['student_id']);
                        $studentOldSecondMarkerDetails = $userDetails->getResponse()[0];
                        $studentOldSecondMarkerUsername = $studentOldSecondMarkerDetails['staff_username'];
                        $oldSecondMarkerStudentUsernames[$studentOldSecondMarkerUsername][] = $studentDetails;

                        if ($studentOldSecondMarkerDetails['staff_id'] == $allocated_staff_id) {
                            // Selected student is already allocated selected second marker
                            $studentClashes[] = $studentDetails['student_first'] . ' ' . $studentDetails['student_last'];
                        } else {
                            // Insert into database
                            if (AllocateStudentSecondMarker($link, $studentUserId, $allocated_staff_id, $staff_id)) {
                                // Send email to student
                                mail(
                                    $studentDetails['student_username'] . '@greenwich.ac.uk',
                                    'Second Marker Allocation Changed',
                                    'You have been allocated to a new second marker, check the eSupervision System for details.',
                                    $emailHeaders
                                );

                                // Send email to new second marker
                                $newSecondMarkerStudentUsernames[] = $studentDetails;
                            } else {
                                $outputText = 'Database error.';
                            }
                        }

                        break;
                }
            }
        }

        // EMAIL OLD STAFF START
        switch ($type) {
            case 'supervisor':
                foreach ($oldSupervisorStudentUsernames as $oldSupervisorUsername => $oldSupervisorStudent) {
                    // Email old supervisor
                    // Create email body text
                    $supervisorEmailBody = 'The following ' . count($oldSupervisorStudent) . ' students have been reallocated to a new supervisor:<ul>';
                    foreach ($oldSupervisorStudent as $student) {
                        $supervisorEmailBody .= '<li>' . $student['student_first'] . ' ' . $student['student_last'] . '</li>';
                    }
                    $supervisorEmailBody .= '</ul>Check the eSupervision System for details.';

                    mail(
                        $oldSupervisorUsername . '@greenwich.ac.uk',
                        'Supervisor Allocation Changed',
                        $supervisorEmailBody,
                        $emailHeaders
                    );
                }

                break;
            case 'second':
                foreach ($oldSecondMarkerStudentUsernames as $oldSecondMarkerUsername => $oldSecondMarkerStudent) {
                    // Email old second marker
                    // Create email body text
                    $secondMarkerEmailBody = 'The following ' . count($oldSecondMarkerStudent) . ' students have been reallocated to a new second marker:<ul>';
                    foreach ($oldSecondMarkerStudent as $student) {
                        $secondMarkerEmailBody .= '<li>' . $student['student_first'] . ' ' . $student['student_last'] . '</li>';
                    }
                    $secondMarkerEmailBody .= '</ul>Check the eSupervision System for details.';

                    mail(
                        $oldSecondMarkerUsername . '@greenwich.ac.uk',
                        'Second Marker Allocation Changed',
                        $secondMarkerEmailBody,
                        $emailHeaders
                    );
                }

                break;

        }
        // EMAIL OLD STAFF END

        // EMAIL NEW STAFF START
        // Get staff username
        $userDetails->GetStaffByStaffId($allocated_staff_id);
        $staffDetails = $userDetails->getResponse()[0];
        $staffUsername = $staffDetails['staff_username'];

        switch ($type) {
            case 'supervisor':
                // Email new supervisor
                // Create email body text
                $supervisorEmailBody = 'You have been allocated as a supervisor to the following ' . count($newSupervisorStudentUsernames) . ' students:<ul>';
                foreach ($newSupervisorStudentUsernames as $student) {
                    $supervisorEmailBody .= '<li>' . $student['student_first'] . ' ' . $student['student_last'] . '</li>';
                }
                $supervisorEmailBody .= '</ul>Check the eSupervision System for details.';

                mail(
                    $staffUsername . '@greenwich.ac.uk',
                    'Supervisor Allocation Changed',
                    $supervisorEmailBody,
                    $emailHeaders
                );

                break;
            case 'second':
                // Email new second marker
                // Create email body text
                $secondMarkerEmailBody = 'You have been allocated as a second marker to the following ' . count($newSecondMarkerStudentUsernames) . ' students:<ul>';
                foreach ($newSecondMarkerStudentUsernames as $student) {
                    $secondMarkerEmailBody .= '<li>' . $student['student_first'] . ' ' . $student['student_last'] . '</li>';
                }
                $secondMarkerEmailBody .= '</ul>Check the eSupervision System for details.';

                mail(
                    $staffUsername . '@greenwich.ac.uk',
                    'Second Marker Allocation Changed',
                    $secondMarkerEmailBody,
                    $emailHeaders
                );

                break;
        }
        // EMAIL NEW STAFF END

        // Redirect user
        if (count($studentClashes) > 0) {
            $_SESSION['clashes'] = $studentClashes;

            // Redirect user
            header('Location: search.php?allocation=' . $type . '&clashes=true');
        } else {
            // Redirect user
            header('Location: search.php?allocation=' . $type);
        }
    }
} else {
    // Save selected students to session
    $_SESSION['allocatedStudents'] = $selectedStudents;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Allocation</title>
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
            $('select').material_select();
        });

        // Client-side form validation
        // Function to display any error messages on form submit
        /**
         * @return {boolean}
         */
        function ValidateForm() {
            var isValid = true;

            // Validate type
            if (ValidateType(document.getElementById('type').value) != '') isValid = false;

            // Validate staff
            if (ValidateStaff(document.getElementById('staff').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the selected type
        function ValidateType(type) {
            var output;
            if (/^\s*$/.test(type)) {
                output = 'You must choose the allocation type';
            } else {
                output = '';
            }

            document.getElementById('typeValidation').innerHTML = output;
            return output;
        }

        // Function to validate the selected staff
        function ValidateStaff(staff) {
            var output;
            if (/^\s*$/.test(staff)) {
                output = 'You must choose a staff member';
            } else {
                output = '';
            }

            document.getElementById('staffValidation').innerHTML = output;
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

    <?php echo $outputText; ?>

    <!-- Start of allocate form -->
    <div class="card">
        <div class="card-content">
            <span class="card-title green-text">Allocation</span>

            <form id="allocationForm" action="allocate.php" method="POST">

                <div class="row">
                    <div class="col s12 m9">
                        <div class="col s12 m6">
                            <label for="type">Allocation type</label>
                            <select name="type" id="type" onkeyup="ValidateType(this.value);"
                                    onblur="ValidateType(this.value);">
                                <option value="" disabled="disabled" selected="selected">Allocation type</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="second">Second marker</option>
                            </select>
                            <span id="typeValidation" class="red-text text-light-3 validation-error"></span>
                        </div>
                        <div class="col s12 m6">
                            <!-- Get programme ID of current students -->
                            <?php if (count($selectedStudents) == 1) {
                                $userDetails->GetStudentDetails($selectedStudents[0]);
                                $studentDetails = $userDetails->getResponse()[0];

                                $programmeId = $studentDetails['programme_id'];
                            } else {
                                $programmeId = $_POST['programme'];
                            } ?>

                            <label for="staff">Select staff</label>
                            <select name="staff" id="staff" onkeyup="ValidateStaff(this.value);"
                                    onblur="ValidateStaff(this.value);">
                                <option value="" disabled="disabled" selected="selected">Select staff</option>
                                <?php
                                // Programme specific staff
                                echo '<option value="" disabled="disabled">&#8212; Programme staff</option>';

                                // Get staff details (same programme only)
                                $userDetails->GetStaffByProgrammeId($programmeId);
                                $staffDetailsProgramme = $userDetails->getResponse();

                                foreach ($staffDetailsProgramme as $staff) {
                                    echo '<option value="' . $staff['staff_id'] . '">' . $staff['staff_first'] . ' ' . $staff['staff_last'] . '</option>';
                                }


                                // Other staff
                                echo '<option value="" disabled="disabled">&#8212; Other staff</option>';

                                // Get all staff details
                                $userDetails->GetAllStaff();
                                $staffDetailsAll = $userDetails->getResponse();

                                foreach ($staffDetailsAll as $staff) {
                                    // Only display if it wasn't displayed in the programme staff section
                                    if (!in_array($staff, $staffDetailsProgramme)) {
                                        echo '<option value="' . $staff['staff_id'] . '">' . $staff['staff_first'] . ' ' . $staff['staff_last'] . '</option>';
                                    }
                                }

                                ?>
                            </select>
                            <span id="staffValidation" class="red-text text-light-3 validation-error"></span>
                        </div>
                    </div>

                    <div class="input-field col s12 m3">
                        <button type="submit" id="saveAllocate" name="saveAllocate" onclick="return ValidateForm();"
                                class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                            Allocate
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
    <!-- End of allocate form -->

    <!-- Students list start -->
    <?php if ($selectedStudents != null) { ?>

        <div class="row center">
            <div class="col s12">
                <h5>Students</h5>
            </div>
        </div>

        <div class="row">

            <?php foreach ($selectedStudents as $studentUsername) {
                // Get current student details
                $userDetails->GetStudentDetails($studentUsername);
                $studentDetails = $userDetails->getResponse()[0];

                // Get current student supervisor
                $userDetails->getStudentSupervisor($studentDetails['student_id']);
                $studentSupervisor = $userDetails->getResponse();

                // Get current student second marker
                $userDetails->getStudentSecondMarker($studentDetails['student_id']);
                $studentSecondMarker = $userDetails->getResponse(); ?>

                <div class="col s12 m6 l4">
                    <div class="card">
                        <div class="card-content">
                                <span class="card-title green-text">
                                    <?php echo $studentDetails['student_first'] . ' ' . $studentDetails['student_last']; ?>
                                </span>

                            <p>
                                Programme: <?php echo $studentDetails['programme_title']; ?>
                                <br/>
                                Supervisor: <?php echo $studentSupervisor[0]['staff_first'] . ' ' . $studentSupervisor[0]['staff_last']; ?>
                                <br/>
                                Second
                                marker: <?php echo $studentSecondMarker[0]['staff_first'] . ' ' . $studentSecondMarker[0]['staff_last']; ?>
                            </p>
                        </div>
                    </div>
                </div>

            <?php } // End foreach ?>

        </div>

    <?php } else {
        echo 'No students selected to allocate.';
    } ?>
    <!-- Students list end -->

</div>

</body>

</html>