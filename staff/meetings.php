<?php

session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id

$c = new Communication ();
if ($_POST['communication_action']){
    try { $c->insert (); }
	catch (Exception $e){
		echo $e->getMessage ();
		return false;
	}
	echo $c->getResponse ();
}

// echo "<pre>";

// print_r ($_POST);

// echo "</pre>";

$c->getAll('message', $stu_user);
$sent = $c->getResponse();
$sent_count = count($sent);

$c->received('message', $stu_user);
$received = $c->getResponse();
$received_count = count($received);

$u = new UserDetails ();
$u->studentSuper($stu_id);
$supervisor = $u->getResponse();


?>


  <title>Meetings</title>
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
                    <a href="dashboard.php">Dashboard</a>
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
 
<!-- MEETING SECTION START -->
        <div>
            <form name="upload" method="post" action='' enctype="multipart/form-data">
                     <p>You have submitted
                <?php echo $meeting_count; ?> Meeting records</p>
                <h2>New Meeting Request</h2>
                <input type='hidden' name='meeting_action' value='request' />
                <input type='hidden' name='meeting_from_id' value='<?php echo $stu_id;?>' />
                <input type='hidden' name='meeting_to_id' value='<?php echo $supervisor[0][' staff_id ']; ?>'/>
                <input type="file" name="fileToUpload" id="fileToUpload">

                <textarea name='communication_body'></textarea>
                <button>Submit</button>
            </form>
            <!-- THE CODE IN foreach GENERATES EACH MEETINGS RECORD AND NEEDS TO BE STYLES AS A COLLECTION ITEM ENCLOSED IN A COLLECTION DIV - SEE MESSAGES SECTION 

THE CODE IN THE PARAGRAPHS BELOW IS A STATIC EXAMPLE OF THE SAME RECORDS; EACH PARAGRAPHS IS TO BE STYLED AS A COLLECTION ITEM AND ENCLOSED WITHIN THE SAME CONTAINER WITH CLASS COLLECTION - FOR TESTING PURPOSES-->
            <p>This is a meeting record example</p>
            <p>This is a meeting record example</p>
            <p>This is a meeting record example</p>
            <p>This is a meeting record example</p>
            <p>This is a meeting record example</p>
            <?php foreach ($meetings as $mg) { echo '<p >'; echo $mg[ 'meeting_title']; echo "</p>"; } ?>
        </div>
        <!-- MEETING SECTION END -->
 
    </div>

</div> <!-- end container -->
</body><script>
$(document).ready(function(){
    $('.modal-trigger').leanModal();
  });
</script>