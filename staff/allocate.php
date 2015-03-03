<?php

// Initialise session
session_start();

//error_reporting(0); todo

require '../login-check.php';
require '../classes/security.class.php';
require '../classes/communication.class.php';
require '../classes/userDetails.class.php';
require '../classes/search.class.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$selectedStudents = $_POST['students'];

if ($currentStaff['staff_authorised'] !== '1') {
    // Do not allow access to unauthorised staff
    header('Location: index.php');
}

$userDetails = new UserDetails ();

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Allocate</title>

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
    <form id="allocationForm" action="allocate.php" method="POST">

        <!-- Start of allocate form -->
        <div class="card">
            <div class="card-content">
                <span class="card-title green-text">Allocation</span>

                <div class="row">

                    <div class="col s12 m9">
                        <!--<label for="name">Student name</label>
                        <input type="search" name="name" id="name" placeholder="Enter a name"
                               value="<?php //echo $_GET['name']; ?>">-->

                        <!--todo-->
                        staff type drop down. staff names dropdown.

                    </div>
                    <div class="input-field col s12 m3">
                        <button type="submit" id="allocate"
                                class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                            Allocate
                        </button>
                    </div>
                </div>
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


                                <?php
                                //todo below
                                //echo '<pre>';
                                //var_dump($studentDetails);
                                //echo '</pre>';
                                ?>



                                <span class="card-title green-text">
                                    <?php echo $studentDetails['student_first'] . ' ' . $studentDetails['student_last']; ?>
                                </span>

                                <p>
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

    </form>

</div>

</body>

</html>