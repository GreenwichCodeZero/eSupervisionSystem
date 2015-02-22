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

$f = $f2 = $f3 = $f4 = $f5 = $f6 = $f7 = new File ();

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


$f2->fileTypes ();
$fileTypes = $f2->getResponse ();

$f3->get ( $stu_user, 'interim');
$interim = $f3->getResponse ();

$f3->get ( $stu_user, 'initial');
$initial = $f3->getResponse ();

$f3->get ( $stu_user, 'ethics');
$ethics = $f3->getResponse ();

$f3->get ( $stu_user, 'proposal');
$proposal = $f3->getResponse ();

$f7->get ( $stu_user, 'project');
$project = $f7->getResponse ();

$u = new UserDetails ();
$u->studentSuper($stu_id);
$supervisor = $u->getResponse();


$f5->supervisorUploads ($supervisor[0]['staff_username'], $stu_user);
$superFiles = $f5->getResponse ();
$super_count = count ($superFiles);

$p->studentProject($stu_user);
$projectTitle = $p->getResponse ();


// // $f->getSubmissions ();

?>


  <title>Submissions</title>
    <link href="../css/styles.css" rel="stylesheet" type="text/css" />
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script>
        function toggleForm(elemID, newButtonID) {
            $(elemID).toggle();
            $(newButtonID).toggle();
        };

        $(document).ready(function () {
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
                    <a href="messages.php">Messages</a>
                </li>
                <li>
                    <a href="meetings.php">Meetings</a>
                </li>
                <li>
                    <a href="blogs.php">Blog/Diary</a>
                </li>
                <li>
                    <a href="submissions.php">Project Uploads</a>
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
        <div class="col s12 m12 l6">
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


        <div class="col s12 m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Project Proposal</span>

                    <p>
                       
                           Latest Upload: 
                           <?php echo ( 
                            $proposal[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$proposal[0]['file_id']."'/><a>".$proposal[0]['file_name']."</a><button>download</button>
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
                            $initial[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$initial[0]['file_id']."'/><a>".$initial[0]['file_name']."</a><button>download</button>
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
                            $interim[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$interim[0]['file_id']."'/><a>".$interim[0]['file_name']."</a><button>download</button>
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
                            $project[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$project[0]['file_id']."'/><a>".$project[0]['file_name']."</a><button>download</button>
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
                            $ethics[0]['file_id'] > 0 ? 
                            "<form action='readfile.php' method='post'><input type='hidden' name='file_id' value='".$ethics[0]['file_id']."'/><a>".$ethics[0]['file_name']."</a><button>download</button>
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

</div> <!-- end container -->
</body><script>
 $(document).ready(function() {
    $('select').material_select();
  });
</script>