<?php

session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';

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

$c->getAll('blog', $stu_user);
$blogs = $c->getResponse();
$sent_count = count($blogs);



$sentcount = 0;

?>

  <title>Communication</title>
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
                    <a href="#">Communication</a>
                </li>
                <li>
                    <a href="#">Meetings</a>
                </li>
                <li>
                    <a href="#">Blog/Diary</a>
                </li>
                <li>
                    <a href="#">Project Uploads</a>
                </li>
            </ul>
            <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
        </div>
    </nav>

    <div class="container">




<?php 
foreach ($blogs as $b) {
	echo "Blog Posts: ";
	echo "message post:". ++$sentcount;
	echo "<pre>";
		print_r ($b);
	echo "</pre>";
}


?>








</div>

</body>