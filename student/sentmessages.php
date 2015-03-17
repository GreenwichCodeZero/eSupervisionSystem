<?php

session_start();

error_reporting(0);

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id

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

$c->getAll('message', $stu_user);
$sent = $c->getResponse();
$sent_count = count($sent);

$c->received('message', $stu_user);
$received = $c->getResponse();
$received_count = count($received);

$u = new UserDetails ();
$u->getStudentSupervisor($stu_id);
$supervisor = $u->getResponse();

?>
<!DOCTYPE html>
<html>
<head>

  <title>eSupervision - Messages</title>
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
            $('.modal-trigger').leanModal();
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
          
     	   <input type="hidden" name="communication_from_id" value="<?php echo $currentUser['student_username']; ?>" ?>
       	 <input type="hidden" name="communication_to_id" value = "<?php echo $supervisor[0]['staff_username']; ?>" />
                <input type='hidden' name='communication_type_id' value='2' />
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
                <span class="card-title green-text">Message History</span>
                <p class="green-text">You have submitted
                    <?php echo $sent_count; ?> Message posts</p>
                <ul class="collection">
                    <?php foreach ($sent as $s) { 
                        echo '<li class="collection-item">'; 
                        echo ' <form action="readfile.php" method="POST">',
                                "<span><p><b> ".$s[ 'communication_body']."</b></p>",
                                "<p>eCommunication added on ". $s['communication_date_added']." at ". $s['communication_time_added']. "</p></span>";
                        
                        if ($s['communication_file_id'] > 0 ) {
                            echo '&emsp; 
                           
                            <input type="hidden" name="file_id" value="'.$s['communication_file_id'].'" />
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
</body>
</html>