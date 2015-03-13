<?php

session_start();

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

    // Get Student uploads by type
    $f->get ($_GET['sid'], 'interim');
    $student_interim = $f->getResponse ();

    $f->get ($_GET['sid'], 'initial');
    $student_initial = $f->getResponse ();

    $f->get ($_GET['sid'], 'ethics');
    $student_ethics = $f->getResponse ();

    $f->get ($_GET['sid'], 'proposal');
    $student_proposal = $f->getResponse ();

    // $f->get ($_GET['sid'], 'contextual');
    // $student_proposal = $f->getResponse ();

    $f->get ($_GET['sid'], 'project');
    $student_project = $f->getResponse ();

    $p->studentProject($_GET['sid']);
    $student_projectTitle = $p->getResponse ();


$superFiles = array 
(    
    "interim" => array 
        ( 
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'interim')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'interim')->getResponse ())
        ),
    "initial" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'initial')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'initial')->getResponse ())
        ), 
    "ethics" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'ethics')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'ethics')->getResponse ())
        ),
    "proposal" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'proposal')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'proposal')->getResponse ())
        ),
    "project" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'project')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'project')->getResponse ())
        ),
    "contextual" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'contextual')->getResponse () ,
        "count" => count ($f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'contextual')->getResponse ())
        ),
    "feedback" => array (
        "files" => $f->supervisorUploads ($supervisor[0]['staff_username'], $stu_user, 'feedback')->getResponse () ,
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


        <div id="submitBlog" class="row">
            <i class="small mdi-content-clear c_right-align" onClick="toggleForm('#submitBlog', '#newBlogEntry');"></i>
            <form name="blogEntry" method="post" action='' enctype="multipart/form-data" class="col s10 m12 offset-s1">

                <input type='hidden' name='file_owner' value='<?php echo $stu_user; ?>' />
                <input type='hidden' name='file_action' value='submit' />
                
                    <select name="file_type_id">
                        <?php foreach ($fileTypes as $ft) {
                                        echo "<option value='".$ft['file_type_id']."'>".$ft['file_type_name']."</option>";
                                    }
                                    ?>
                  </select>

                          <input class="waves-effect waves-teal waves-light btn-flat" type="file" name="fileToUpload" id="fileToUpload">
             
                <button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Submit</button>
            </form>
        </div>


    <div class="row">
  <a onClick="toggleForm('#submitBlog', '#newBlogEntry');" id="newBlogEntry" class="c_right-align"> 
                <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Submit new file</div>
            </a>
    </div>     



    <div class="row">
            <div class="card">
            <form method = 'post'>
                <div class="card-content">
                    <span class="card-title green-text">Project Title</span>

                    <p>
                        <input type='text' name ='title' placeholder='<?php echo ( $projectTitle ? $projectTitle[0]['project_title'] : 'Insert title ...' ); ?>' />
                        <input type='hidden' name='projectDetails_action' value = 'projectDetails_action' />
                        <button>Update</button>
                    </p>
                </div>
                </form>
            </div>
        

    </div>
    <div class="row">
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                     <span class="card-title
                                    green-text">Project Proposal</span>

                                    <p>
                                       
                                           Latest Upload: 
                                           <?php echo ( 
                                            $student_proposal[0]['file_id'] > 0 ? 
                                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_proposal[0]['file_id']."'/><a>".$student_proposal[0]['file_name']."</a><button>download</button>
                                            </form>"
                                            : "no file uploaded yet" 
                                            );  
                                        ?>
                                    </p>

                                   <hr />
                                    <p>
                                        <?php 
                                
                                if ($superFiles['proposal']['count'] > 0) {
                                    foreach ($superFiles['proposal']['files'] as $sf) { 
                                        
                                        echo '<li class="collection-item">'; 

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3); 

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
                                        }                  

                                        
                                  
                                } else {
                               

                                     echo ' <li class="collection-item">
                                    You have not uploaded anything
                                    </li> ';
                                
                                }
                                ?></p>
                </div>
            </div>
        </div>


        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Contextual Report</span>

                    <p>
                       
                           Latest Upload: 
                           <?php echo ( 
                            $contextual[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$contextual[0]['file_id']."'/><a>".$contextual[0]['file_name']."</a><button>download</button>
                            </form>"
                            : "no file uploaded yet" 
                            );  
                        ?>
                    </p>
                    <hr />
                                    <p>
                                        <?php 
                                
                                if ($superFiles['contextual']['count'] > 0) {
                                    foreach ($superFiles['contextual']['files'] as $sf) { 
                                        
                                        echo '<li class="collection-item">'; 

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3); 

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
                                        }                  

                                        
                                  
                                } else {
                               

                                     echo ' <li class="collection-item">
                                    You have not uploaded anything
                                    </li> ';
                                
                                }
                                ?></p>
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
                        
                           Latest Upload: 
                           <?php echo ( 
                            $initial[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$initial[0]['file_id']."'/><a>".$initial[0]['file_name']."</a><button>download</button>
                            </form>"
                            : "no file uploaded yet" 
                            );  
                        ?>
                    </p>
                   <hr />
                                    <p>
                                        <?php 
                                
                                if ($superFiles['initial']['count'] > 0) {
                                    foreach ($superFiles['initial']['files'] as $sf) { 
                                        
                                        echo '<li class="collection-item">'; 

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3); 

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
                                        }                  

                                        
                                  
                                } else {
                               

                                     echo ' <li class="collection-item">
                                    You have not uploaded anything
                                    </li> ';
                                
                                }
                                ?></p>
                </div>
            </div>
        </div>


        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Interim Report</span>

                    <p>
                           Latest Upload: 
                           <?php echo ( 
                            $interim[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$interim[0]['file_id']."'/><a>".$interim[0]['file_name']."</a><button>download</button>
                            </form>"
                            : "no file uploaded yet" 
                            );  
                        ?>
                    </p>
                 <hr />
                                    <p>
                                        <?php 
                                
                                if ($superFiles['interim']['count'] > 0) {
                                    foreach ($superFiles['interim']['files'] as $sf) { 
                                        
                                        echo '<li class="collection-item">'; 

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3); 

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
                                        }                  

                                        
                                  
                                } else {
                               

                                     echo ' <li class="collection-item">
                                    You have not uploaded anything
                                    </li> ';
                                
                                }
                                ?></p>

                    
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Project Report</span>

                    <p>
                           Latest Upload: 
                           <?php echo ( 
                            $project[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$project[0]['file_id']."'/><a>".$project[0]['file_name']."</a><button>download</button>
                            </form>"
                            : "no file uploaded yet" 
                            );  
                        ?>
                    </p>
                   <hr />
                                    <p>
                                        <?php 
                                
                                if ($superFiles['project']['count'] > 0) {
                                    foreach ($superFiles['project']['files'] as $sf) { 
                                        
                                        echo '<li class="collection-item">'; 

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3); 

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
                                        }                  

                                        
                                  
                                } else {
                               

                                     echo ' <li class="collection-item">
                                    You have not uploaded anything
                                    </li> ';
                                
                                }
                                ?></p>
                </div>
            </div>
        </div>


        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Research Ethics</span>

                    <p>
                           Latest Upload: 
                           <?php echo ( 
                            $ethics[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$ethics[0]['file_id']."'/><a>".$ethics[0]['file_name']."</a><button>download</button>
                            </form>"
                            : "no file uploaded yet" 
                            );  
                        ?>
                    </p>
                    <hr />
                                    <p>
                                        <?php 
                                
                                if ($superFiles['ethics']['count'] > 0) {
                                    foreach ($superFiles['ethics']['files'] as $sf) { 
                                        
                                        echo '<li class="collection-item">'; 

                                        $date = strtotime($sf['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($sf['communication_time_added'], 0, -3); 

                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
                                        }                  

                                        
                                  
                                } else {
                               

                                     echo ' <li class="collection-item">
                                    You have not uploaded anything
                                    </li> ';
                                
                                }
                                ?></p>

                   
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
 <div class="col s10 m12 offset-s1 card">
            
            <div class="card-content">
                <span class="card-title green-text">Supervisor Uploads</span>
                <p class="green-text"><?php echo $supervisor[0]['staff_first']." ".$supervisor[0]['staff_last'];?> has uploaded
                    <?php echo $super_count; ?> files</p>
                
                <ul class="collection">
                <?php 
                if ($super_count > 0) {
?>

                
                    <?php foreach ($superFiles as $sf) { 
                        if ($sf['file_type_id'] == 1) {

                                echo '<li class="collection-item">'; 
                                echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                    <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                    <button>download</button></form>";
                                    
                                echo "</li>";
                            } else {
                                echo "your supervisor has not uploaded anything";
                            }
                        }
                        

                        
                    ?>
                </ul>

                <?php 
            } else {
                ?>

                <li class="collection-item">
                No files uploaded
                </li> 

                <?php 

            }

?>
    </ul>
            </div>
        </div>
    </div>

</div> <!-- end container -->
</body>
</html>