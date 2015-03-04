<?php

session_start();

session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
$currentStaff = $_SESSION['currentUser'];

include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/comment.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';
include '../classes/projectDetails.class.php';

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$staff_username = $currentStaff['staff_username'];


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

// Get allocated students
$u = new UserDetails ();
$u->GetAllocatedStudents($staff_username);
$students = $u->getResponse();

print_r($students);
// Is staff authorised
$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach($getStaffDetails as $staffDetail){
    $staffAuthorsied = $staffDetail['staff_authorised'];
}
// End is staff authorised

// Determine which messages to display
if ($_GET['sid']) {
    
    $filtered = 1;

    // Get files uploaded by type
    $f->fileTypes ();
    $fileTypes = $f->getResponse ();

    // Get Student uploads by type
    $f->get ($_GET['sid'], 'interim');
    $student_interim = $f->getResponse ();

        // Get Staff uploads by type
        $f->supervisorUploads ($staff, $student, 'interim');
        $staff_interim = $f->getResponse ();

    $f->get ($_GET['sid'], 'initial');
    $student_initial = $f->getResponse ();

        // Get Staff uploads by type
        $f->supervisorUploads ($staff, $student, 'initial');
        $staff_initial = $f->getResponse ();

    $f->get ($_GET['sid'], 'ethics');
    $student_ethics = $f->getResponse ();

        // Get Staff uploads by type
        $f->supervisorUploads ($staff, $student, 'ethics');
        $staff_ethics = $f->getResponse ();

    $f->get ($_GET['sid'], 'proposal');
    $student_proposal = $f->getResponse ();

        // Get Staff uploads by type
        $f->supervisorUploads ($staff, $student, 'proposal');
        $staff_proposal = $f->getResponse ();

    $f->get ($_GET['sid'], 'project');
    $student_project = $f->getResponse ();

        // Get Staff uploads by type
        $f->supervisorUploads ($staff, $student, 'project');
        $staff_project = $f->getResponse ();

    $p->studentProject($_GET['sid']);
    $student_projectTitle = $p->getResponse ();

    //  is this needed?
    $f->getAll($_GET['sid']);
    $files = $f->getResponse();
    $file_count = count($files);
} else {
    $filtered = -1;
}





?>
<head>
    <title>Student Uploads</title>
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
				<?php
            if($staffAuthorsied == 1){
                echo '<li><a href="search.php">Search</a></li>
                    <li><a href="viewDashboards.php">View dashboards</a></li>';
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
    
 <div class="row">


        <div class="col s10 m12 offset-s1 card">
          

            <div class="card-content">
                <span class="card-title green-text">Student Upload History</span>
                <?php if ($filtered) {
                    // Get student name
                    $ud = new UserDetails ();
                    $ud->GetStudentDetails($_GET['sid']);
                    $student = $ud->getResponse();

                    if ( !isset($_GET['sid']) ) {
                        echo '<p>Your allocated students have submitted '. $file_count .' blog posts collectively.</p>';
                    } else {

                        echo '<p>'. $student[0]['student_first'] . ' ' . $student[0]['student_last'] . ' has submitted '. $file_count .' blog posts.</p>';
                    }


                } ?>

                <div class="row">
                    <!-- STUDENT FILTER FORM START -->
                    <form id="communication_filter" action="" method="GET">
                        <div class="col s12 m9">
                            <label for="communication_student_id_filter">Select a student</label>
                            <select name="sid" id="communication_student_id_filter">
                                <option value="" disabled="disabled" selected="selected">Choose...</option>
                                <?php foreach ($students as $stu) {
                                    echo '<option value="' . $stu['student_username'] . '"' . (($_GET['sid'] == $stu['student_username']) ? 'selected="selected"' : '') . '>' . $stu['student_first'] . ' ' . $stu['student_last'] . ' (' . $stu['student_username'] . ') </option>';
                                } ?>
                            </select>
                        </div>
                        <div class="input-field col s12 m3">

                            <button type="submit"
                                    class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">
                                Filter
                            </button>
                        </div>
                    </form>
                    <!-- STUDENT FILTER FORM END -->
                </div>

                <?php if ($filtered > 0) { ?>

                    
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
                        <div class="col s12 m12 l6">
                            <div class="card">
                            <form method = 'post'>
                                <div class="card-content">
                                    <span class="card-title green-text">Project Title</span>

                                    <p>
                                        <input type='text' name ='title' placeholder='<?php echo ( $student_projectTitle ? $student_projectTitle[0]['project_title'] : 'Insert title ...' ); ?>' />
                                        <input type='hidden' name='projectDetails_action' value = 'projectDetails_action' />
                                        <button>Update</button>
                                    </p>
                                </div>
                                </form>
                            </div>
                        </div>


                        <div class="col s12 m12 l6">
                            <div class="card">
                                <div class="card-content">
                                    <span class="card-title green-text">Project Proposal</span>

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

                                    <p>Staff
                                         <?php echo ( 
                                            $staff_proposal[0]['file_id'] > 0 ? 
                                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$staff_proposal[0]['file_id']."'/><i class='mdi-file-attachment'></i><a>".$staff_proposal[0]['file_name']."</a><button>download</button>
                                            </form>"
                                            : "no file uploaded yet" 
                                            );  
                                        ?>
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
                                        
                                           Latest Upload: 
                                           <?php echo ( 
                                            $student_initial[0]['file_id'] > 0 ? 
                                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_initial[0]['file_id']."'/><a>".$student_initial[0]['file_name']."</a><button>download</button>
                                            </form>"
                                            : "no file uploaded yet" 
                                            );  
                                        ?>
                                    </p>
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
                                            $student_interim[0]['file_id'] > 0 ? 
                                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_interim[0]['file_id']."'/><a>".$student_interim[0]['file_name']."</a><button>download</button>
                                            </form>"
                                            : "no file uploaded yet" 
                                            );  
                                        ?>
                                    </p>

                                    
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
                                            $student_project[0]['file_id'] > 0 ? 
                                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_project[0]['file_id']."'/><a>".$student_project[0]['file_name']."</a><button>download</button>
                                            </form>"
                                            : "no file uploaded yet" 
                                            );  
                                        ?>
                                    </p>
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
                                            $student_ethics[0]['file_id'] > 0 ? 
                                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$student_ethics[0]['file_id']."'/><a>".$student_ethics[0]['file_name']."</a><button>download</button>
                                            </form>"
                                            : "no file uploaded yet" 
                                            );  
                                        ?>
                                    </p>

                                   
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
                                        
                                        echo '<li class="collection-item">'; 
                                        echo ' <form action="readfile.php" method="POST">', "<p>{$sf['communication_body']}</p><a> {$sf[ 'file_name']}</a>                    
                                            <input type='hidden' name='file_id' value='".$sf['file_id']."' />
                                            <button>download</button></form>";
                                            
                                        echo "</li>";
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




                  <?php  } else if ($blog_count == 0) {

                    // No messages found for current student
                    
                    echo '<ul class="collection"><li class="collection-item">No posts to display</li></ul>';
                } ?>
            </div>
        </div>
        <!--MESSAGING SECTION END-->
    </div>     



 
</div>
<!-- end container -->
</body>

<script>
    $(document).ready(function () {
        $('.modal-trigger').leanModal();
    });
</script>