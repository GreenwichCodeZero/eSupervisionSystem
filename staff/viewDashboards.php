<?php
// Initialise session
session_start();

error_reporting(0);

require '../login-check.php';
require '../classes/security.class.php';
require '../classes/communication.class.php';
require '../classes/userDetails.class.php';
require '../classes/search.class.php';

// Redirect students
if ($_SESSION['currentUser']['user_type'] == 'student') {
    header ('location: ../student');
}

// Globals
$currentStaff = $_SESSION['currentUser'];
$staff_id = $currentStaff['staff_id'];
$staff_username = $currentStaff['staff_username'];

// Determine permissions of current user
if ($currentStaff['user_type'] === 'student') {
    // Redirect to staff dashboard
    header('Location: /codezero/student/index.php');
} else if ($currentStaff['staff_authorised'] !== '1') {
    // Do not allow access to unauthorised staff
    header('Location: index.php');
}

$userDetails = new UserDetails ();

$getAllUnauthorisedStaffQ = new UserDetails ();
$getAllUnauthorisedStaffQ->getAllUnauthorisedStaff();
$getAllUnauthorisedStaffs = $getAllUnauthorisedStaffQ->getResponse();

$getAllProjectStudentsQ = new UserDetails();
$getAllProjectStudentsQ->GetAllocatedStudents($staff_username);
$getAllProjectStudents = $getAllProjectStudentsQ->getResponse();

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach ($getStaffDetails as $staffDetail) {
    $staffAuthorised = $staffDetail['staff_authorised'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Search</title>
    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
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
                <a href="uploads.php">Project Uploads</a>
            </li>

            <?php
            if ($staffAuthorsied == 1) {
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
			if ($staffAuthorsied == 1) {
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

  <h1>View dashboards</h1>

<form action="staffDashboard.php" method="get">
<select id="staff" name="staff">
<option value="0">Please select a staff</option>
<?php
foreach($getAllUnauthorisedStaffs as $getAllUnauthorisedStaff){
    echo '<option value="' . $getAllUnauthorisedStaff['staff_id'] . '">' . $getAllUnauthorisedStaff['staff_first'] . " " . $getAllUnauthorisedStaff['staff_last'] . "</option>";
}
?>
</select>

<input type="submit" id="staffSubmit" onclick="return validateStaffSearch();" name="staffSubmit" value="View staff dashboard">
</form>


<form action="studentDashboard.php" method="get">
<select id="student" name="student">
<option value="0">Please select a student</option>
<?php
foreach($getAllProjectStudents as $getAllProjectStudent){
    echo '<option value="' . $getAllProjectStudent['student_id'] . '">' . $getAllProjectStudent['student_first'] . " " . $getAllUnauthorisedStaff['student_last'] . "</option>";
}
?>
</select>

<input type="submit" id="studentSubmit" onclick="return validateStudentSearch();" name="studentSubmit" value="View student dashboard">
</form>

<div id="validation"></div>

</div>

<script type="text/javascript">

    function validateStaffSearch(){
        var staffSelectedValue = document.getElementById('staff').selectedIndex;
        var staffSelected = false;

        if(staffSelectedValue != 0){
            staffSelected = true;
         }else{
            staffSelected = false;
            document.getElementById('validation').innerHTML = "Please select a staff";
         }
    return staffSelected;
    }

        function validateStudentSearch(){
            var studentSelectedValue = document.getElementById('student').selectedIndex;
            var studentSelected = false;

            if(studentSelectedValue != 0){
                studentSelected = true;
             }else{
                document.getElementById('validation').innerHTML = "Please select a student";
                studentSelected = false;
             }
        return studentSelected;
    }

</script>
</body>

</html>