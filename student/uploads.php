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

$f = new File ();
if ($_POST['file_action']){
    $el = new errorList ();

    try { $f->submit ( $stu_user, $_POST['file_type_id']); }
    catch (Exception $e){
        
        $el->newList()->type('error')->message ($e->getMessage ())->go('uploads.php');
        exit;
    }
    
    $el->newList()->type('success')->message ($c->getResponse ())->go('messages.php');
    exit;

}

$f->getAll($stu_user);
$files = $f->getResponse();
$file_count = count($files);

$f2 = new File ();
$f2->fileTypes ();
$fileTypes = $f2->getResponse ();

$u = new UserDetails ();
$u->studentSuper($stu_id);
$supervisor = $u->getResponse();

// $f->getSubmissions ();

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
                    <a href="messages.php">Communication</a>
                </li>
                <li>
                    <a href="meetings.php">Meetings</a>
                </li>
                <li>
                    <a href="blogs.php">Blog/Diary</a>
                </li>
                <li>
                    <a href="uploads.php">Project Uploads</a>
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
        <div class="col s10 m12 offset-s1 card">
            
            <div class="card-content">
                <span class="card-title green-text">Upload History</span>
                <p class="green-text">You have uploaded
                    <?php echo $file_count; ?> files</p>
                <ul class="collection">
                
                    <?php foreach ($files as $f) { 
                        
                        echo '<li class="collection-item">'; 
                        echo ' <form action="readfile.php" method="POST">', "<p> {$f[ 'file_name']} </p>                      
                            <input type='hidden' name='file_id' value='".$f['file_id']."' />
                            <button>View file</button>";
                            
                        echo "</form>","</li>";
                        }
                        

                        
                    ?>
                </ul>
            </div>
        </div>

        <!-- BLOG SECTION END -->
    </div>

</div> <!-- end container -->
</body><script>
 $(document).ready(function() {
    $('select').material_select();
  });
</script>