<?php

session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id

// $f = new File ();
// if ($_POST['file_action']){
//     $el = new errorList ();

//     try { $f->submit ( $stu_user, $_POST['file_type_id']); }
//     catch (Exception $e){
        
//         $el->newList()->type('error')->message ($e->getMessage ())->go('uploads.php');
//         exit;
//     }
    
//     $el->newList()->type('success')->message ($c->getResponse ())->go('messages.php');
//     exit;

// }

// $f->getAll($stu_user);
// $files = $f->getResponse();
// $file_count = count($files);

// $f2 = new File ();
// $f2->fileTypes ();
// $fileTypes = $f2->getResponse ();

// $u = new UserDetails ();
// $u->studentSuper($stu_id);
// $supervisor = $u->getResponse();

// // $f->getSubmissions ();

?>
<!DOCTYPE html>
<html>

<head>

	<title>eSupervision - Submissions</title>
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
					<a href="submissions.php">Project Uploads</a>
				</li>
				<li>
					<a href="../logout.php" title="Logout">Logout</a>
				</li>
			</ul>

			<ul id="nav-mobile">
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
					<a href="submissions.php">Project Uploads</a>
				</li>
				<li>
					<a href="../logout.php" title="Logout">Logout</a>
				</li>
			</ul>
			<a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
		</div>
	</nav>
<div class="container">
    <div class="row">
        <!-- Uploads SECTION START -->
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
    </div>

    <div class="row">
        <h5 class="center-align">Project Uploads</h5>
    </div>
    <div class="row">
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Project Title</span>

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
                    <span class="card-title green-text">Project Proposal</span>

                    <p>
                        Supervisor: <?php echo "<a href=" . '"' . $supervisor[0]['staff_profile_link'] . '" target="_blank">' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . "</a>"; ?>
                    </p>

                    <p>
                        Second
                        Marker: <?php echo "<a href=" . '"' . $secondMarker[0]['staff_profile_link'] . '" target="_blank">' . $secondMarker[0]['staff_first'] . ' ' . $secondMarker[0]['staff_last'] . "</a>"; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Inital Report</span>

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
                    <span class="card-title green-text">Interim Report</span>

                    <p>
                        Supervisor: <?php echo "<a href=" . '"' . $supervisor[0]['staff_profile_link'] . '" target="_blank">' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . "</a>"; ?>
                    </p>

                    <p>
                        Second
                        Marker: <?php echo "<a href=" . '"' . $secondMarker[0]['staff_profile_link'] . '" target="_blank">' . $secondMarker[0]['staff_first'] . ' ' . $secondMarker[0]['staff_last'] . "</a>"; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Final Report</span>

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
                    <span class="card-title green-text">Research Ethics
                    Form</span>

                    <p>
                        Supervisor: <?php echo "<a href=" . '"' . $supervisor[0]['staff_profile_link'] . '" target="_blank">' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . "</a>"; ?>
                    </p>

                    <p>
                        Second
                        Marker: <?php echo "<a href=" . '"' . $secondMarker[0]['staff_profile_link'] . '" target="_blank">' . $secondMarker[0]['staff_first'] . ' ' . $secondMarker[0]['staff_last'] . "</a>"; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    


</div> <!-- end container -->
</body>
</html>