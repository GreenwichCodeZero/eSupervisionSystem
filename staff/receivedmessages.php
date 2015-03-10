<?php

session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

$sta_id = $currentUser['staff_id']; // (1) = demo staff id
$sta_user = $currentUser['staff_username']; // (1) = demo staff id

$c = new Communication ();
if ($_POST['communication_action']){
    $el = new errorList ();

    try { $c->insert ( $stu_user ); }
	catch (Exception $e){
        
        $el->newList()->type('error')->message ($e->getMessage ())->go('messages.php');
		exit;
	}
	
    $el->newList()->type('success')->message ($c->getResponse ())->go('messages.php');
    exit;

}

$c->received($sta_user, 'staff');
$received = $c->getResponse();
$received_count = count($received);

$u = new UserDetails ();
$u->getStudentSupervisor($sta_id);
$supervisor = $u->getResponse();

$u = new UserDetails ();
$u->GetAllocatedStudents($sta_user);
$students = $u->getResponse();

?>


	<title>Messages</title>
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
                <a href="submissions.php">Project Uploads</a>
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
 <!-- MESSAGE SECTION START-->

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

        <div id="sendMessage" class="row">
            <i class="small mdi-content-clear c_right-align" onClick="toggleForm('#sendMessage', '#newMessage');"></i>
            <form id='communication' action='' method='POST' enctype="multipart/form-data" class="col s10 m12 offset-s1">
                <input type='hidden' name='communication_action' value='sendmessage' />
          
           <input type="hidden" name="communication_from_id" value="<?php echo $sta_user; ?>" ?>


             

                <input type='hidden' name='communication_type_id' value='2' />
                    <label>Select a student</label>
                  <select name="communication_to_id">
                        <?php foreach ($students as $stu) {
                                        echo "<option value='".$stu['student_username']."'>".$stu['student_first']." ".$stu['student_last']." (".$stu['student_username'].") </option>";
                                    }
                                    ?>
                  </select>
                <div class="input-field">
                    <textarea class="materialize-textarea" name='communication_body'></textarea>
                    <label>New Message</label>
                </div>

                          <input class="waves-effect waves-teal waves-light btn-flat" type="file" name="fileToUpload" id="fileToUpload">


                <button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Submit</button>
            </form>
        </div>

        <div class="col s10 m12 offset-s1 card">
            <a onClick="toggleForm('#sendMessage', '#newMessage');" class="c_right_align" id="newMessage">
                <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">New Message</div>
            </a>
            <div class="card-content">
                <span class="card-title green-text">Messages you have received</span>
                <p class="green-text">You have received
                    <?php echo $received_count; ?> Message posts</p>
                <ul class="collection">
                    <?php foreach ($received as $r) { 
                        echo '<li class="collection-item">'; 
                        echo ' <form action="readfile.php" method="POST">',
                                "<span><p><b> ".$r[ 'communication_body']."</b></p>",
                                "<p>eCommunication sent by ".$r['student_first']." ".$r['student_last']." on ". $r['communication_date_added']." at ". $r['communication_time_added']. "</p></span>";
                        
                        if ($r['communication_file_id'] > 0 ) {
                            echo '&emsp; 
                           
                            <input type="hidden" name="file_id" value="'.$r['communication_file_id'].'" />
                             <button class="btn waves-light" >Submit
                                View <i class="mdi-content-send right"></i> </button>';
                        }
                        
                        echo "</form>","</li>"; 
                    } ?>
                </ul>
            </div>
        </div>
        <!--MESSAGING SECTION END-->
    </div>

</div> <!-- end container -->
</body><script>
$(document).ready(function() {
    $('select').material_select();
  });
</script>