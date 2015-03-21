<?php

session_start();

error_reporting(0);

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';
include '../classes/projectDetails.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id

$f = new File ();

if ($_POST['file_action']){
    $el = new errorList ();

    try { $f->submit ( $stu_user, $_POST['file_type_id']); }
    catch (Exception $e){

        $el->newList()->type('error')->message ($e->getMessage ())->go('submissions.php');
        exit;
    }

    $el->newList()->type('success')->message ($f->getResponse ())->go('submissions.php');
    exit;

}

$p = new projectDetails ();

if ($_POST['projectDetails_action']){
    $el = new errorList ();

    try { $p->newTitle ( $stu_user); }
    catch (Exception $e){

        $el->newList()->type('error')->message ($e->getMessage ())->go('submissions.php');
        exit;
    }

    $el->newList()->type('success')->message ($p->getResponse ())->go('submissions.php');
    exit;

}


$u = new UserDetails ();
$u->getStudentSupervisor($stu_id);
$supervisor = $u->getResponse();


$f->fileTypes ();
$fileTypes = $f->getResponse ();
// print_r($fileTypes);

// Get Student uploads by type
$f->get ($stu_user, 'interim');
$student_interim = $f->getResponse ();

$f->get ($stu_user, 'initial');
$student_initial = $f->getResponse ();

$f->get ($stu_user, 'ethics');
$student_ethics = $f->getResponse ();

$f->get ($stu_user, 'proposal');
$student_proposal = $f->getResponse ();

$f->get ($stu_user, 'contextual');
$student_contextual = $f->getResponse ();

$f->get ($stu_user, 'feedback');
$student_feedback = $f->getResponse ();

$f->get ($stu_user, 'project');
$student_project = $f->getResponse ();

$p->studentProject($stu_user);
$student_projectTitle = $p->getResponse ();


$superFiles = array
(
    "interim" => array
    (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'interim', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'interim')->getResponse ())
    ),
    "initial" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'initial', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'initial')->getResponse ())
    ),
    "ethics" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'ethics', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'ethics')->getResponse ())
    ),
    "proposal" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'proposal', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'proposal')->getResponse ())
    ),
    "project" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'project', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'project')->getResponse ())
    ),
    "contextual" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'contextual', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'contextual')->getResponse ())
    ),
    "feedback" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'feedback', ' limit 1')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'feedback')->getResponse ())
    )
);

// print_r($superFiles);
$p->studentProject($stu_user);
$projectTitle = $p->getResponse ();


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

        <ul id="nav-mobile" class="side-nav hide-on-large-only" style="overflow-y: scroll;">
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
                <p class='<?php echo $el->getType (); ?>' a>
                    <?php echo $el->getResponse (); ?>
                </p>
            <?
            }
            ?>
        </div>
    </div>
	
	<div class="row">
		<div class="col s10 m12 offset-s1 card">
			<a onClick="toggleForm('#submitUpload', '#newUpload');" id="newUpload" class="c_right-align">
				<div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Submit new file</div>
			</a>
			<div class="card-content">
				<span class="card-title green-text">Project Uploads</span>
				<div id="submitUpload">
					<i class="small mdi-content-clear c_right-align" onClick="toggleForm('#submitUpload', '#newUpload');"></i>
					<form name="uploadEntry" method="post" action='' enctype="multipart/form-data" class="col s10 m12 offset-s1">
						<input type='hidden' name='file_owner' value='<?php echo $stu_user; ?>' />
						<input type='hidden' name='file_action' value='submit' />
						<select name="file_type_id">
							<?php foreach ($fileTypes as $ft) {
								echo "<option value='".$ft['file_type_id']."'>".$ft['file_type_name']."</option>";
							}
							?>
						</select>
						<div class="waves-effect waves-teal waves-light green btn-flat white-text">
							<span>File</span>
							<input type="file" name="fileToUpload" id="fileToUpload"/>
						</div>
						<p><strong>Uploads are restricted to PDF and a maximum of 40MB</strong></p>
						<button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Submit</button>
					</form>
				</div>
			</div>
			<div class="col s12">
				<div class="card">
					<form method = 'post'>
						<div class="card-content">
							<span class="card-title green-text">Project Title</span>
							<div>
								<input type='text' name ='title' placeholder='<?php echo ( $projectTitle ? $projectTitle[0]['project_title'] : 'Insert title ...' ); ?>' />
								<input type='hidden' name='projectDetails_action' value = 'projectDetails_action' />
								<button class='c_right-align waves-effect waves-teal waves-light  green btn-flat white-text'>Update</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="col s12">
				<div class="card">
					<div class="card-content">
						<span class="card-title green-text">Project Proposal</span>
						<div class='section'>Latest Upload:
							<ul class="collection">
								<?php
								echo "<li class='collection-item'>";
								echo ($student_proposal[0]['file_id'] > 0 ?
									"<form action='readfile.php' method='post' style='min-height: 35px;'><input type='hidden' name='file_id' value='".$student_proposal[0]['file_id']."'/><a>".$student_proposal[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button></form>"
									: "You have not uploaded anything yet"
								);
								echo "</li>";

								if ($superFiles['proposal']['count'] > 0) {
									foreach ($superFiles['proposal']['files'] as $sf) {

										echo '<li class="collection-item">';
										$date = strtotime($sf['communication_date_added']);
										$prettyDate = date('l j F Y', $date);

										// Output date and time
										echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

										echo '<form action="readfile.php" method="POST" style="min-height: 35px;">', "<p>{$sf['communication_body']}</p><a>{$sf[ 'file_name']}</a>
										<button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>
										<input type='hidden' name='file_id' value='".$sf['file_id']."'/>
											</form>";

                                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo '<li class="collection-item grey lighten-3">
									Your tutor has not uploaded anything yet
									</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Contextual Report</span>
                        <div>Latest Uploads:
                            <ul class="collection">
                                <?php


                                $date = strtotime($student_contextual[0]['file_date_added']);
                                $prettyDate = date('l j F Y', $date);


                                echo "<li class='collection-item'>";
                                echo ($student_contextual[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_contextual[0]['file_id']."'/><a>".$student_contextual[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>

									<p><b>File uploaded on ".$prettyDate . " at " . substr($student_contextual[0]['file_time_added'], 0, -3)." </b></p></form>"
                                    : "You have not uploaded anything yet"
                                );
                                if ($superFiles['contextual']['count'] > 0) {
                                    foreach ($superFiles['contextual']['files'] as $sf) {
                                        echo '<li class="collection-item">';
                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
												<input type='hidden' name='file_id' value='".$sf['file_id']."' />
												<button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-tex'><i class='mdi-file-file-download icon'></i></button></form>";
                                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="collection-item grey lighten-3">
										Your tutor has not uploaded anything yet
										</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Initial Report</span>
                        <div>Latest Uploads:
                            <ul class="collection">
                                <?php

                                $date = strtotime($student_initial[0]['file_date_added']);
                                $prettyDate = date('l j F Y', $date);

                                echo "<li class='collection-item'>";
                                echo ($student_initial[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_initial[0]['file_id']."'/><a>".$student_initial[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>

									<p><b>File uploaded on ".$prettyDate . " at " . substr($student_initial[0]['file_time_added'], 0, -3)."</b></p>
								</form>"
                                    : "You have not uploaded anything yet"
                                );

                                if ($superFiles['initial']['count'] > 0) {
                                    foreach ($superFiles['initial']['files'] as $sf) {

                                        echo '<li class="collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);



                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
												<input type='hidden' name='file_id' value='".$sf['file_id']."' />
												<button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download icon'></i></button></form>";




                                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";		echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="collection-item grey lighten-3">
										Your tutor has not uploaded anything yet
										</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Interim Report</span>
                        <div>Latest Uploads:
                            <ul class="collection">
                                <?php
                                $date = strtotime($student_interim[0]['file_date_added']);
                                $prettyDate = date('l j F Y', $date);

                                echo "<li class='collection-item'>";
                                echo ($student_interim[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_interim[0]['file_id']."'/><a>".$student_interim[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>

									<p><b>File uploaded on ".$prettyDate . " at " . substr($student_interim[0]['file_time_added'], 0, -3)."</b></p>
								</form>"
                                    : "You have not uploaded anything yet"
                                );
                                if ($superFiles['interim']['count'] > 0) {
                                    foreach ($superFiles['interim']['files'] as $sf) {

                                        echo '<li class="collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
													<input type='hidden' name='file_id' value='".$sf['file_id']."' />
													<button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button></form>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="collection-item grey lighten-3">
											Your tutor has not uploaded anything yet
									</li> ';
                                }?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Project Report</span>
                        <div>Latest Uploads:
                            <ul class="collection">
                                <?php


                                $date = strtotime($student_project[0]['file_date_added']);
                                $prettyDate = date('l j F Y', $date);
                                echo "<li class='collection-item'>";

                                echo ($student_project[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_project[0]['file_id']."'/><a>".$student_project[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>

									<p><b>File uploaded on ".$prettyDate . " at " . substr($student_project[0]['file_time_added'], 0, -3)."</b></p>
								</form>"
                                    : "You have not uploaded anything yet"
                                );
                                if ($superFiles['project']['count'] > 0) {
                                    foreach ($superFiles['project']['files'] as $sf) {

                                        echo '<li class="collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);



                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
												<input type='hidden' name='file_id' value='".$sf['file_id']."' />
												<button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download icon'></i></button>

												</form>";
                                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="collection-item grey lighten-3">
									Your tutor has not uploaded anything yet
									</li> ';
                                }?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Formative Feedback</span>
                        <div>Latest Uploads:
                            <ul class="collection">
                                <?php

                                $date = strtotime($student_feedback[0]['file_date_added']);
                                $prettyDate = date('l j F Y', $date);

                                echo "<li class='collection-item'>";
                                echo ($student_feedback[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_feedback[0]['file_id']."'/><a>".$student_feedback[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light  green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>

									<p><b>File uploaded on ".$prettyDate . " at " . substr($student_feedback[0]['file_time_added'], 0, -3)."</b></p>"
                                    : "You have not uploaded anything yet"
                                );
                                if ($superFiles['feedback']['count'] > 0) {
                                    foreach ($superFiles['feedback']['files'] as $sf) {

                                        echo '<li class="collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);



                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
												<input type='hidden' name='file_id' value='".$sf['file_id']."' />
												<button class='c_right-align waves-effect waves-teal waves-light  green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button></form>";



                                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";	echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="collection-item grey lighten-3">
										Your tutor has not uploaded anything yet
										</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Research Ethics</span>
                        <div>Latest Uploads:
                            <ul class="collection">
                                <?php
                                $date = strtotime($student_ethics[0]['file_date_added']);
                                $prettyDate = date('l j F Y', $date);

                                echo "<li class='collection-item'>";
                                echo ($student_ethics[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_ethics[0]['file_id']."'/><a>".$student_ethics[0]['file_name']."</a><button class='c_right-align waves-effect waves-teal waves-light green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button>

									<p><b>File uploaded on ".$prettyDate . " at " . substr($student_ethics[0]['file_time_added'], 0, -3)."</b></p>
								</form>"
                                    : "You have not uploaded anything yet"
                                );

                                if ($superFiles['ethics']['count'] > 0) {
                                    foreach ($superFiles['ethics']['files'] as $sf) {

                                        echo '<li class="collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);



                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
												<input type='hidden' name='file_id' value='".$sf['file_id']."' />
												<button class='c_right-align waves-effect waves-teal waves-light  green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button></form>";



                                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";	echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="collection-item grey lighten-3">
										Your tutor has not uploaded anything yet
										</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col s10 m12 offset-s1 card">
            <div class="card-content">
                <span class="card-title green-text">Supervisor Uploads</span>
                <p class="green-text"><?php echo $supervisor[0]['staff_first']." ".$supervisor[0]['staff_last'];?> has uploaded <?php echo count($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user)->getResponse ()); ?> files</p>


                <?php
                if (count($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user)->getResponse ()) > 0) {

                    foreach ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user)->getResponse () as $sf) {

                        $date = strtotime($sf['communication_date_added']);
                        $prettyDate = date('l j F Y', $date);
                        // if ($sf['file_type_id'] == 1) {
                        echo '<ul class="collection">';
                        echo '<li class="collection-item ">';
                        echo ' <form action="readfile.php" method="POST">', "<a> {$sf[ 'file_name']}</a>
										<input type='hidden' name='file_id' value='".$sf['file_id']."' />
										<button class='c_right-align waves-effect waves-teal waves-light  green btn-flat white-text icon'><i class='mdi-file-file-download'></i></button></form>";

                        echo "<p><b>File uploaded on ".$prettyDate . ' at ' . substr($sf['communication_time_added'], 0, -3), "</b></p>";
                        echo "</li>";
                        echo '</ul>';
                        // }
                    }
                } else {
                    ?>
                    <ul class="collection">
                        <li class="collection-item grey lighten-3">
                            No files have been uploaded by your supervisor
                        </li>
                    </ul>
                <?php
                }
                ?>
                </ul>
            </div>
        </div>

    </div>
</div>
<!-- end container -->
</body>
</html>