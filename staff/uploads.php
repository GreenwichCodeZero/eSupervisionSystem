<?php

// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/comment.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';
include '../classes/projectDetails.class.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$staff_username = $currentStaff['staff_username'];

$f = new File ();

if ($_POST['action'] == "filter"){
    $el = new errorList ();

    if (empty ($_POST['sid'])) {
        $errorMessage = "Please select a student";
        $el->newList()->type('error')->message($errorMessage)->go('uploads.php');
        exit;
    }

}

if ($_POST['file_action']){
    $el = new errorList ();
    $c = new Communication ();

    try {
        $c->insert($staff_username, $_POST['file_type_id']);
    } catch (Exception $e) {

        $el->newList()->type('error')->message($e->getMessage())->go('uploads.php?sid='.$_POST['sid']);
        exit;
    }

    $el->newList()->type('success')->message($c->getResponse())->go('uploads.php?sid='.$_POST['sid']);
    exit;
}

$p = new projectDetails ();

if ($_POST['projectDetails_action']){
    $el = new errorList ();

    try { $p->newTitle ( $stu_user); }
    catch (Exception $e){

        $el->newList()->type('error')->message ($e->getMessage ())->go('uploads.php?sid='.$_POST['sid']);
        exit;
    }

    $el->newList()->type('success')->message ($p->getResponse ())->go('uploads.php?sid='.$_POST['sid']);
    exit;

}

// Get allocated students
$u = new UserDetails ();
$u->GetAllocatedStudents($staff_username);
$students = $u->getResponse();

$currentStudent = $u->singleStudent ($_POST['sid'])->getResponse ();
// print_r ($currentStudent);

// Determine which messages to display
if ($_POST['sid']) {

    $f->fileTypes ();
    $fileTypes = $f->getResponse ();

    $filtered = 1;

    // Get files uploaded by type
    $f->fileTypes ();
    $fileTypes = $f->getResponse ();

    // Get Student uploads by type
    $f->get ($_POST['sid'], 'interim');
    $student_interim = $f->getResponse ();

    $f->get ($_POST['sid'], 'initial');
    $student_initial = $f->getResponse ();

    $f->get ($_POST['sid'], 'ethics');
    $student_ethics = $f->getResponse ();

    $f->get ($_POST['sid'], 'proposal');
    $student_proposal = $f->getResponse ();

    $f->get ($_POST['sid'], 'contextual');
    $student_contextual = $f->getResponse ();

    $f->get ($_POST['sid'], 'project');
    $student_project = $f->getResponse ();


    $f->get ($_POST['sid'], 'feedback');
    $student_feedback = $f->getResponse ();

    $p->studentProject($_POST['sid']);
    $student_projectTitle = $p->getResponse ();



    $superFiles = array
    (
        "interim" => array
        (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'interim')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'interim')->getResponse ())
        ),

        "initial" => array (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'initial')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'initial')->getResponse ())
        ),
        "ethics" => array (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'ethics')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'ethics')->getResponse ())
        ),
        "proposal" => array (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'proposal')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'proposal')->getResponse ())
        ),
        "project" => array (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'project')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'project')->getResponse ())
        ),
        "contextual" => array (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'contextual')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'contextual')->getResponse ())
        ),
        "feedback" => array (
            "files" => $f->supervisorUploads ($staff_username, $_POST['sid'], 'feedback')->getResponse () ,
            "count" => count ($f->supervisorUploads ($staff_username, $_POST['sid'], 'feedback')->getResponse ())
        )
    );

// Get Staff uploads by type
// echo "<pre>";
// print_r ($studentFiles);
// echo "</pre>";


    //  is this needed?
    $f->getAll($_POST['sid']);
    $files = $f->getResponse();
    $file_count = count($files);
} else {
    $filtered = -1;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Student Uploads</title>
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
        ;

        $(document).ready(function () {
            $('select').material_select();
            $(".button-collapse").sideNav();
        });
    </script>
    <style>
        .m-7 {
            margin-top: -7px;
        }

        .f-staff {
            background: #fafafa !important;
        }
    </style>
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
    <div>
        <?php
        $el = new errorList ();
        if ($el->exists ()){
            ?>
            <p class='<?php echo $el->getType (); ?>' >
                <?php echo $el->getResponse (); ?>
            </p>
        <?
        }
        ?>
    </div>
    <div class="row">
        <div class="col s10 m12 offset-s1 card">
            <div class="card-content">
                <span class="card-title green-text">Student Upload History</span>


                <!-- STUDENT FILTER FORM START -->
                <form id="communication_filter" action="" method="POST">
                    <label for="communication_student_id_filter">Select a student</label>
                    <select name="sid" id="communication_student_id_filter">
                        <option value="" disabled="disabled" selected="selected">Choose...</option>
                        <?php foreach ($students as $stu) {
                            echo '<option value="' . $stu['student_username'] . '"' . (($_POST['sid'] == $stu['student_username']) ? 'selected="selected"' : '') . '>' . $stu['student_first'] . ' ' . $stu['student_last'] . ' (' . $stu['student_username'] . ') </option>';
                        }
                         ?>
                    </select>
                    <input type='hidden' name='action' value='filter' />
                    <button type="submit" class="c_right-align waves-effect waves-teal waves-light blue btn-flat white-text">Filter</button>
                </form>
                <!-- STUDENT FILTER FORM END -->

                <?php if ($filtered > 0) { ?>
                <!-- START UPLOADS BY STUDENT -->


                <!-- Uploads SECTION START -->
                <div class="col s12">
                        <?php foreach ($students as $stu) { 
                            if ($stu['student_username'] == $_POST['sid']) {
                        ?>
                    <h5 class="center-align">Project Uploads for <?php echo $stu['student_first'], ' ',$stu['student_last'] .' (',$stu['student_username'],')'; ?></h5>

                    <?php } // end if                       
                    } // end foreach
                    ?>
                    <br /><br />
                </div>

                <?php if (! isset ($_GET['type'])) {   ?>

                <div class="col s12">

                    <div id="submitBlog">
                        <i class="small mdi-content-clear c_right-align" onClick="toggleForm('#submitBlog', '#newBlogEntry');"></i>
                        <!-- NEW FILE UPLOAD FORM START -->
                        <form id="communication" method="POST" action="" enctype="multipart/form-data">
                            <input type='hidden' name='file_action' value='uploadfile'/>
                            <input type='hidden' name='communication_action' value='sendmessage' />
                            <input type="hidden" name="communication_from_id" value="<?php echo $staff_username; ?>">
                            <input type='hidden' name='communication_type_id' value='2'/>
                            <div class="col s6">
                                <input type='hidden' name="communication_to_id" value='<?php echo $_POST['sid']; ?>'>
                                <select name="file_type_id">
                                    <?php foreach ($fileTypes as $ft) {
                                        echo "<option value='".$ft['file_type_id']."'>".$ft['file_type_name']."</option>";
                                    }?>
                                </select>
                            </div>
                            <div class="input-field col s12">
                                <label for="communication_body">Upload </label>
                                <input type='hidden' name="communication_body" value = '### FILE UPLOAD - no content ###' />
                            </div>
                            <div class="file-field input-field col s12">
                                <div class="waves-effect waves-teal waves-light green btn-flat white-text">
                                    <span>File</span>
                                    <input type="file" name="fileToUpload" id="fileToUpload"/>
                                </div>

                            <p><strong>Uploads are restricted to PDF and a maximum of 40MB</strong></p>
                            </div>

                            <div>
                            </div>

                            <div class="input-field">
                                <button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- NEW UPLOAD FORM END -->

            </div>
            <br/>

            <div class="col s12" style="background-color: #fafafa; margin-bottom: 10px; border: thin solid #ccc;">
                <span class="card-title green-text">Supervisor Uploads</span>
                <div class='c_right-align'>
                    <a onClick="toggleForm('#submitBlog', '#newBlogEntry');" id="newBlogEntry">
                        <button class="waves-effect waves-teal waves-light green btn-flat white-text">Submit new file</button>
                    </a>
                </div>

                <p>You have uploaded <?php $f = new File(); count ($f->supervisorUploads ($staff_username, $_POST['sid'])->getResponse ()); ?> files with feedback</p>
               

            </div>


            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Project Title</span>
                        <div>
                            <h5><?php echo ( $student_projectTitle ? ucfirst ($student_projectTitle[0]['project_title']) : 'A title has not yet been submitted for this project'); ?></h5>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=3" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>

                        <span class="card-title green-text">Project Proposal</span>

                        <div class='section'>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_proposal[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_proposal[0]['file_id']."'/>
                                    <a>".$student_proposal[0]['file_name']."</a>
												<button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
												</form>"
                                    : $currentStudent['student_first']." has not uploaded anything yet" );
                                echo "</li>";

                                if ($superFiles['proposal']['count'] > 0) {
                                    foreach ($superFiles['proposal']['files'] as $sf) {

                                        echo '<li class="collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                                        echo ' <form action="readfile.php" method="POST">', "<div>{$sf['communication_body']}</div><a> {$sf[ 'file_name']}</a>
															<input type='hidden' name='file_id' value='".$sf['file_id']."' />
															<button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";

                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
													You have not uploaded anything yet
													</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=4" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>
                        <span class="card-title green-text">Contextual Report</span>
                        <div>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_contextual[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_contextual[0]['file_id']."'/>
                                    <a>".$student_contextual[0]['file_name']."</a> <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
													</form>"
                                   : $currentStudent['student_first']." has not uploaded anything yet"
                                );

                                if ($superFiles['contextual']['count'] > 0) {
                                    foreach ($superFiles['contextual']['files'] as $sf) {

                                        echo '<li class="f-staff collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                                        echo ' <form action="readfile.php" method="POST">', 
                                        "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
															<input type='hidden' name='file_id' value='".$sf['file_id']."' />
															<button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
															</form>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
													You have not uploaded anything yet
													</li> ';
                                }?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=8" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>
                        <span class="card-title green-text">Inital Report</span>
                        <div>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_initial[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_initial[0]['file_id']."'/>
                                    <a>".$student_initial[0]['file_name']."</a>
												 <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
												</form>"
                                    : $currentStudent['student_first']." has not uploaded anything yet"
                                );
                                if ($superFiles['initial']['count'] > 0) {
                                    foreach ($superFiles['initial']['files'] as $sf) {

                                        echo '<li class=" f-staff collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                                        echo ' <form action="readfile.php" method="POST">', 
                                        "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
															<input type='hidden' name='file_id' value='".$sf['file_id']."' />
															 <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
													You have not uploaded anything yet
													</li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=5" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>
                        <span class="card-title green-text">Interim Report</span>

                        <div>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_interim[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_interim[0]['file_id']."'/>
                                    <a>".$student_interim[0]['file_name']."</a> <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
												</form>"
                                    : $currentStudent['student_first']." has not uploaded anything yet"
                                );

                                if ($superFiles['interim']['count'] > 0) {
                                    foreach ($superFiles['interim']['files'] as $sf) {

                                        echo '<li class="f-staff collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                                        echo ' <form action="readfile.php" method="POST">', 
                                        "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
														<input type='hidden' name='file_id' value='".$sf['file_id']."' />
														<button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
												You have not uploaded anything yet
												</li> ';
                                }?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6">
               <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=2" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>
                        <span class="card-title green-text">Project Report</span>

                        <div>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_project[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_project[0]['file_id']."'/>
                                    <a>".$student_project[0]['file_name']."</a> <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
                                                </form>"
                                    : $currentStudent['student_first']." has not uploaded anything yet"
                                );

                                if ($superFiles['project']['count'] > 0) {
                                    foreach ($superFiles['project']['files'] as $sf) {

                                        echo '<li class="f-staff collection-item">';

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo ' <form action="readfile.php" method="POST">'
                                        , "<p>", $prettyDate , ",", substr(($sf['communication_time_added']), 0, -3),
                                         "</p><a> {$sf[ 'file_name']}</a>
                                                        <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                        <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
                                                You have not uploaded anything yet
                                                </li> ';
                                }?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=6" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>
                        <span class="card-title green-text">Research Ethics</span>
                        <div>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_ethics[0]['file_id'] > 0 ?
                                    "<form class='action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_ethics[0]['file_id']."'/>
                                    <a>".$student_ethics[0]['file_name']."</a>
                                                 <button class='c_right-align waves-effect m-7 m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
                                                </form>"
                                    : $currentStudent['student_first']." has not uploaded anything yet</li>");
                                if ($superFiles['ethics']['count'] > 0) {
                                    foreach ($superFiles['ethics']['files'] as $sf) {

                                        echo '<li class="f-staff collection-item">';
                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                                        echo ' <form action="readfile.php" method="POST">', 
                                        "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class='waves-effect waves-teal waves-light  green btn-flat white-text' ><i class='mdi-file-file-download'></i></button></form>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
                                                    You have not uploaded anything yet
                                                    </li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-content">
                        <div class='c_right-align'>
                            <form action="?type=6" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                                <button type="submit" class="waves-effect waves-teal waves-light orange lighten-2 btn-flat white-text">VIEW ALL</button>
                            </form>
                        </div>
                        <span class="card-title green-text">Formative Feedback</span>
                        <div>Latest Uploads: &emsp; Total uploads <?php echo count ( $totaluploads ); ?>
                            <ul class="collection">
                                <?php
                                echo "<li class='collection-item'>";
                                echo (
                                $student_feedback[0]['file_id'] > 0 ?
                                    "<form action='readfile.php' method='post'>
                                    <input type='hidden' name='file_id' value='".$student_feedback[0]['file_id']."'/>
                                    <a>".$student_feedback[0]['file_name']."</a>
                                                 <button class='c_right-align waves-effect m-7 waves-teal waves-light green btn-flat white-text'><i class='mdi-file-file-download'></i></button>
                                                </form>"
                                    : $currentStudent['student_first']." has not uploaded anything yet</li>");
                                if ($superFiles['feedback']['count'] > 0) {
                                    foreach ($superFiles['feedback']['files'] as $sf) {

                                        echo '<li class="f-staff collection-item">';
                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3);

                            echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>
                                    <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                    <button class='c_right-align waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";

                            echo "</li>";
                                    }
                                } else {
                                    echo ' <li class="f-staff collection-item">
                                                    You have not uploaded anything yet
                                                    </li> ';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>



           <?php }  else {

            $studentFiles = array
            (
                "interim" => array
                (
                    "files" => $f->getAll($_POST['sid'], 'interim')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'interim')->getResponse ())
                ),

                "initial" => array (
                    "files" => $f->getAll($_POST['sid'], 'initial')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'initial')->getResponse ())
                ),
                "ethics" => array (
                    "files" => $f->getAll($_POST['sid'], 'ethics')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'ethics')->getResponse ())
                ),
                "proposal" => array (
                    "files" => $f->getAll($_POST['sid'], 'proposal')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'proposal')->getResponse ())
                ),
                "project" => array (
                    "files" => $f->getAll($_POST['sid'], 'project')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'project')->getResponse ())
                ),
                "contextual" => array (
                    "files" => $f->getAll($_POST['sid'], 'contextual')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'contextual')->getResponse ())
                ),
                "feedback" => array (
                    "files" => $f->getAll($_POST['sid'], 'feedback')->getResponse () ,
                    "count" => count ($f->getAll($_POST['sid'], 'feedback')->getResponse ())
                )
            );

            ?>
            <div class="row">
                <div class="col s12"  style='background-color: #fafafa; margin-bottom: 10px; border: thin solid #ccc;'>
                    <div class='c_right-align'>
                        <form action="uploads.php" method="post" >
                            <input type="hidden" name="sid" value="<?php echo $_POST['sid'];?>" />
                            <button type="submit" class="waves-effect waves-teal waves-light blue lighten-1 btn-flat white-text">GO BACK</button>
                        </form>
                    </div>

                        <?php
                        foreach ($fileTypes as $ft) {

                            // check file type exists
                            if ($_GET['type'] == $ft['file_type_id']) {

                                // Decipher FILE TYPE and echo header
                                $file_filter = $ft['file_type_name'];
                                echo '<span class="card-title green-text">'.$ft['file_type_name'].'</span>';


                            } // End If filetype exist

                        } // End foreach

                        switch  ($_GET['type'])
                        {
                            case 1:
                                if (is_array ($studentFiles['feedback']['files'])) {
                                    foreach ($studentFiles['feedback']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item">';
                                        echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;
                            case 2:
                                if (is_array ($studentFiles['project']['files'])) {
                                    foreach ($studentFiles['project']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item" >';
                                        echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;
                            case 3:
                                if (is_array ($studentFiles['proposal']['files'])) {
                                    foreach ($studentFiles['proposal']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item">';
                                        echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;
                            case 4:
                                if (is_array ($studentFiles['contextual']['files'])) {
                                    foreach ($studentFiles['contextual']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item">';
                                        echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;
                            case 5:
                                if (is_array ($studentFiles['interim']['files'])) {
                                    foreach ($studentFiles['interim']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item">';
                                        echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;
                            case 6:
                                if (is_array ($studentFiles['ethics']['files'])) {
                                    foreach ($studentFiles['ethics']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item">';
                                        echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;
                            case 8:
                                if (is_array ($studentFiles['initial']['files'])) {
                                    foreach ($studentFiles['initial']['files'] as $file) {
                                        echo '<ul class="collection"><li class="collection-item">';
                                                                                echo "<p>".$file['date_added']. " - " . $file['time_added']."</p>";
                                        echo ' <form action="readfile.php" method="POST">', "<p><a> {$file[ 'file_name']}</a>
                                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                                             <button class=' waves-effect waves-teal waves-light  green btn-flat white-text'><i class='mdi-file-file-download'></i></button></form>";
                                        //
                                        echo "</li></ul>";

                                    } // End Foreach
                                } else { echo "There are no uploads of this type"; }// End Is Array
                                break;

                        }

                        ?>
                </div>
            </div>
        </div>

        <?php }  // END FILTER BY TYPE
        }

        ?>



    </div>
</div>
</div>


</div>
<!-- end container -->
</body>
<script>
$('form').submit(function(){
    $('button').remove ();
});
</script>
</html>