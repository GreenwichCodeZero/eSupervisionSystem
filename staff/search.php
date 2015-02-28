<?php
session_start();
$submit = 0;

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
$currentStaff = $_SESSION['currentUser'];

include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/search.class.php';
include 'searchCheck.php';

$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id
$staff_id = $currentStaff['staff_id'];

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($staff_id);
$getStaffDetails = $getStaffDetailsQ->getResponse();

$searchProgrammesQ = new Search ();
$searchProgrammesQ->searchProgrammes();
$searchProgrammes = $searchProgrammesQ->getResponse();

$searchStudentsByProgrammeQ = new UserDetails ();
$searchStudentsByProgrammeQ->searchStudentsByProgramme($programmeID);
$searchStudentsByProgrammes = $searchStudentsByProgrammeQ->getResponse();

foreach($getStaffDetails as $staffDetail){
    $staffAuthorsied = $staffDetail['staff_authorised'];
}

if($staffAuthorsied != 1){  //quick fix to not allow access to unauthorised staff
    header('Location: index.php');
} 

$searchStudentsQ = new UserDetails ();
$searchStudentsQ->searchStudents($name);
$searchStudents = $searchStudentsQ->getResponse();
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
<h1>Search</h1>
<!-- Start of search by name -->
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<label for="searchByName">Enter a first or full name:</label>
<input type="search" name="searchByName" id="searchByName" placeholder="Enter a students name to search">

<input type="submit" name="searchSubmit" id="searchSubmit" value="Search by name">
</form>
<!-- End of search by name -->
<br>
<p>OR</p>
<!-- Start of search by programme -->

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<label for="searchProgramme">Search students by programme:</label>
<select name="searchProgramme" id="searchProgramme">
<option>Please select a programme</option>
<?php
foreach($searchProgrammes as $programme){
    echo "<option value=" . '"' . $programme['programme_id'] . '">' . $programme['programme_title'] . "</option>";
}
?>

</select>

<input type="submit" name="searchProgrammeSubmit" id="searchSubmit" value="Search by programme">
</form>
<!-- End of search by programme -->

<!-- Start of display students found by searching their name -->
<?php
if($submit == 1 && $searchStudents != null){
    echo "<h2>Studets found:</h2>";

foreach($searchStudents as $students){
     echo'<div class="row">
        <div class="col s12 m10">
          <div class="card">
            <div class="card-content green-text">
              <span class="card-title green-text">';
                  echo $students['student_first'] . " " . $students['student_last'];
                  echo "</span> <br>";
$studentSupervisorQ = new UserDetails ();
$studentSupervisorQ->getStudentSupervisor($students['student_id']);
$studentSupervisors = $studentSupervisorQ->getResponse();
foreach($studentSupervisors as $studentSupervisor){
    echo "Supervisor = " . $studentSupervisor['staff_first'] . " " . $studentSupervisor['staff_last'];
}   
$studentSecondMarkerQ = new UserDetails ();
$studentSecondMarkerQ->getStudentSecondMarker($students['student_id']);
$studentSecondMarkers = $studentSecondMarkerQ->getResponse();
foreach($studentSecondMarkers as $studentSecondMarker){
    echo "<br> Second marker = " . $studentSecondMarker['staff_first'] . " " . $studentSecondMarker['staff_last'];
}    
        echo'  
           </div>
            <div class="card-action">
              <a href="#">This is a link</a>
            </div>
          </div>
        </div>
      </div>';
}
}else{
    echo $noStudentsFound;
}
//End of display students found by searching their name -->

//Start of display students found by programme -->

if($searchStudentsByProgrammes != null){
    echo "<h2>Studets found:</h2>";

foreach($searchStudentsByProgrammes as $studentsProgramme){
    echo'<div class="row">
        <div class="col s12 m10">
          <div class="card">
            <div class="card-content green-text">
              <span class="card-title green-text">';
                  echo $studentsProgramme['student_first'] . " " . $studentsProgramme['student_last'];
            echo'  </span><br>';
$studentSupervisorQ = new UserDetails ();
$studentSupervisorQ->getStudentSupervisor($studentsProgramme['student_id']);
$studentSupervisors = $studentSupervisorQ->getResponse();
foreach($studentSupervisors as $studentSupervisor){
    echo "Supervisor = " . $studentSupervisor['staff_first'] . " " . $studentSupervisor['staff_last'];
}    
$studentSecondMarkerQ = new UserDetails ();
$studentSecondMarkerQ->getStudentSecondMarker($studentsProgramme['student_id']);
$studentSecondMarkers = $studentSecondMarkerQ->getResponse();
foreach($studentSecondMarkers as $studentSecondMarker){
    echo "<br> Second marker = " . $studentSecondMarker['staff_first'] . " " . $studentSecondMarker['staff_last'];
}          
echo' </div>
            <div class="card-action">
              <a href="#">Select student (Just an example of how this could work for US17 perhaps)</a>
            </div>
          </div>
        </div>
      </div>';
}
}else{
    echo $noProgrameStudents;
}
?>
<!-- End of display students found by programme -->

</div>

</body>

</html>