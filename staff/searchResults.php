<?php
session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
$currentStaff = $_SESSION['currentUser'];

include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id
$staff_id = $currentStaff['staff_id'];




$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach($getStaffDetails as $staffDetail){
    $staffAuthorsied = $staffDetail['staff_authorised'];
}

if($staffAuthorsied != 1){  //quick fix to not allow access to unauthorised staff
    header('Location: index.php');
} 
?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Search results</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
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
				echo '<li>
						<a href="search.php">Search</a>
					</li>';
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
<h1>Search results</h1>

<h2><?php echo $name; ?></h2>
</form>

</div>

</body>

</html>