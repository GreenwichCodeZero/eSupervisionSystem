<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
require '../classes/security.class.php';
require '../classes/communication.class.php';
require '../classes/userDetails.class.php';
require '../classes/search.class.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];

if ($currentStaff['staff_authorised'] !== '1') {
    // Do not allow access to unauthorised staff
    header('Location: index.php');
}

$userDetails = new UserDetails ();

// Get search results
$name = $searchStudentsByName = $programmeID = $searchStudentsByProgramme = null;
if (isset($_GET['name'])) {
    $name = $_GET['name'];

    if ($name == null) {
        $submit = 0;
        $noStudentsFound = 'Please enter a students name';
    } else {
        $noStudentsFound = 'No students found by the name "' . $name . '"';
    }

    $userDetails->searchStudents($name);
    $searchStudentsByName = $userDetails->getResponse();
} else if (isset($_GET['programme'])) {
    $programmeID = $_GET['programme'];

    if ($programmeID == null) {
        $noStudentsFound = 'Please select a programme';
    } else {
        $noStudentsFound = "No students found on this programme";
    }

    $userDetails->searchStudentsByProgramme($programmeID);
    $searchStudentsByProgramme = $userDetails->getResponse();
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Search</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
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
            <?php if ($currentStaff['staff_authorised'] === '1') {
                echo '<li><a href="search.php">Search</a></li>';
            } ?>
            <li>
                <a href="../logout.php" title="Logout">Logout</a>
            </li>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>

<div class="container">
    <!-- Search start -->
    <div class="card">
        <div class="card-content">
            <span class="card-title green-text">Search Students</span>

            <div class="row">

                <!-- Start of search by name -->
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">

                    <div class="col s12 m9">
                        <label for="name">Student name</label>
                        <input type="search" name="name" id="name" placeholder="Enter a name"
                               value="<?php echo $_GET['name']; ?>">
                    </div>
                    <div class="input-field col s12 m3">
                        <button type="submit" id="searchSubmit"
                                class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                            Search
                        </button>
                    </div>
                </form>
                <!-- End of search by name -->

            </div>

            <div class="row center">
                <p><b>or</b></p>
            </div>

            <div class="row">

                <!-- Start of search by programme -->
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">

                    <div class="col s12 m9">
                        <label for="programme">Student programme</label>
                        <select name="programme" id="programme">
                            <option value="">Select a programme</option>
                            <?php
                            $search = new Search ();
                            $search->searchProgrammes();
                            $searchProgrammes = $search->getResponse();

                            foreach ($searchProgrammes as $programme) {
                                echo '<option value="' . $programme['programme_id'] . '"' . (($_GET['programme'] == $programme['programme_id']) ? 'selected="selected"' : '') . '>' . $programme['programme_title'] . "</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="input-field col s12 m3">
                        <button type="submit" id="searchProgrammeSubmit"
                                class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                            Search
                        </button>
                    </div>
                </form>
                <!-- End of search by programme -->

            </div>
        </div>
    </div>
    <!-- Search end -->

    <!-- Results start -->
    <?php if ($name != null || $programmeID != null) { ?>

        <div class="row center">
            <div class="col s12">
                <h5>Results</h5>
            </div>
        </div>

        <form id="allocationForm" action="allocate.php" method="POST">

            <div class="row">

                <?php

                if ($name != null) {
                    $students = $searchStudentsByName;
                } else {
                    $students = $searchStudentsByProgramme;
                }

                foreach ($students as $student) {
                    $userDetails->getStudentSupervisor($student['student_id']);
                    $studentSupervisors = $userDetails->getResponse();

                    $userDetails->getStudentSecondMarker($student['student_id']);
                    $studentSecondMarkers = $userDetails->getResponse(); ?>

                    <div class="col s12 m6 l4">
                        <div class="card">
                            <div class="card-content">
                            <span class="card-title green-text">
                                <?php echo $student['student_first'] . ' ' . $student['student_last']; ?>
                            </span>

                                <p>
                                    Programme: <?php echo $student['programme_title']; ?>
                                    <br/>
                                    Supervisor: <?php echo $studentSupervisors[0]['staff_first'] . ' ' . $studentSupervisors[0]['staff_last']; ?>
                                    <br/>
                                    Second
                                    marker: <?php echo $studentSecondMarkers[0]['staff_first'] . ' ' . $studentSecondMarkers[0]['staff_last']; ?>
                                </p>
                            </div>
                            <div class="card-action">

                                <?php if ($programmeID != null) {
                                    // Searched by programme, select many ?>
                                    <input value="<?php echo $student['student_username']; ?>" name="students[]"
                                           type="checkbox"
                                           id="<?php echo $student['student_username']; ?>"/>
                                    <label for="<?php echo $student['student_username']; ?>"
                                           class="green-text">Select</label>
                                <?php } else {
                                    // Searched by name, select single ?>
                                    <input value="<?php echo $student['student_username']; ?>" name="students[]"
                                           type="radio" id="<?php echo $student['student_username']; ?>"
                                           class="with-gap"/>
                                    <label for="<?php echo $student['student_username']; ?>"
                                           class="green-text">Select</label>
                                <?php } ?>

                            </div>
                        </div>
                    </div>

                <?php } // End foreach ?>

            </div>

            <div class="row">
                <input type="hidden" name="programme" value="<?php echo $programmeID; ?>"/>

                <div class="input-field col s12">
                    <button type="submit" name="allocate"
                            class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                        Allocate
                    </button>
                </div>
            </div>
        </form>

    <?php } else {
        echo $noStudentsFound;
    } ?>
    <!-- Results end -->

</div>

</body>

</html>