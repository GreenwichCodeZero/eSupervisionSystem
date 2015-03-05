<?php
// A dashboard placeholder, demonstrating access to the current user

// Initialise session
session_start();

require '../login-check.php';
include '../classes/userDetails.class.php';
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/meetings.class.php';

//$currentStaff = $_SESSION['currentUser'];
$newStaffId = $_GET['staff'];

$newCurrentStaffQ = new UserDetails();
$newCurrentStaffQ->getNewStaffDetails($newStaffId);
$newCurrentStaff = $newCurrentStaffQ->getResponse();

//echo "new staff id = " . $newStaffId;

$userDetails = '';

// Determine permissions of current user
    // All staff only things here
    $staffDetails = 'Staff: '. $newCurrentStaff[0]['staff_username'];
	$staffName = $newCurrentStaff[0]['staff_first'] . ' '. $newCurrentStaff[0]['staff_last'];

    if ($newCurrentStaff[0]['staff_authorised'] === '1') {
        // Authorised staff only things here
        $userDetails .= '<li>staff_authorised: yes</li>';
    } else {
        // Unauthorised staff only things here
        $userDetails .= '<li>staff_authorised: no</li>';
    }



// $_SESSION['user']['id']
$staff_username = $newCurrentStaff[0]['staff_username']; // (1) = demo staff id
$staff_id = $newCurrentStaff[0]['staff_id'];

// PRINT USER VARIABLES TO TOP OF BROWSER


$c = new Communication ();

$c->getAll('message', $staff_username, 'staff');
$messages = $c->getResponse();
$message_count = count($messages);

$c->received($staff_username, 'staff');
$received = $c->getResponse();
$received_count = count($received);

$m = new Meeting ();
$m->getAll($staff_username);
$meetings = $m->getResponse();
$meeting_count = count($meetings);

$studentsSupervised = new UserDetails ();
$studentsSupervised->supervisorStudents($newStaffId);
$students = $studentsSupervised->getResponse();

$noSupervisorQ = new UserDetails ();
$noSupervisorQ->noSupervisor();
$noSupervisors = $noSupervisorQ->getResponse();

$noSecondMarkerQ = new UserDetails ();
$noSecondMarkerQ->noSecondMarker();
$noSecondMarkers = $noSecondMarkerQ->getResponse();

$getStaffDetailsQ = new UserDetails ();
$getStaffDetailsQ->isStaffAuthorised($newStaffId);
$getStaffDetails = $getStaffDetailsQ->getResponse();

foreach($getStaffDetails as $staffDetail){
    $staffAuthorsied = $staffDetail['staff_authorised'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Dashboard</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--<link href="css/styles.css" rel="stylesheet" type="text/css"/>-->
    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".button-collapse").sideNav();
            $(".dropdown-button").dropdown();
        });
    </script>
</head>

<body>
<nav>
    <div class="nav-wrapper green">
        <ul id="nav-mobile" class="side-nav">
            <li>
                <a href="#">Dashboard</a>
            </li>
            <li>
                <a href="#">Meetings</a>
            </li>
            <li>
                <a href="#">Messages</a>
            </li>
            <li>
                <a href="#">Blog</a>
            </li>
            <li>
                <a href="#">Project Uploads</a>
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
        <h4 class="center-align">You are viewing staff: <?php echo $newCurrentStaff[0]['staff_first'] . " " . $newCurrentStaff[0]['staff_last'] . "s "; ?>eSupervision Dashboard</h4>
            <h5 class="center-align"><a href="viewDashboards.php">Go back to view dashboards</a></h5>
    </div>
    <div class="row">
        <div class="col s10 offset-s1 m8 offset-m2 l6 offset-l3 center-align">
			<div>
				<?php echo $staffDetails; ?> 
			</div>
			<div>
				<?php echo $staffName; ?>
			</div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Meeting Summary</span>
                    <p>You have submitted <?php echo $meeting_count; ?> meeting records.</p>
                </div>
                <div class="card-action">
                    <a href="#" title="View all meetings">View All</a>
                    <a href="#" title="Request new meeting">Request</a>
                </div>
            </div>
        </div>
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Message Summary</span>
                    <p>You have submitted <?php echo $message_count; ?> messages.</p>
                    <p>You have received <?php echo $received_count; ?> messages.</p>
                </div>
                <div class="card-action">
                    <a href="#" title="View all messages">View All</a>
                    <a href="#newMessageModal" title="Write new message">Message Project Students</a>
                </div>
            </div>
        </div>
    
        <!-- new stuff -->

        <!--  Project students starts here -->
        <div class="col s12 l6">
			<div class="card">
				<div class="card-content">
					<span class="card-title green-text">List of project students</span>
					<div class="collection">
						<?php
							foreach ($students as $student) {
								echo "<a class='collection-item' href='#'>". $student['student_first'] . " " . $student['student_last'] . "</a>";
							}
						?>       
					</div>
				</div>
			</div>
        </div>
        <!--  Project students ends here -->

    <!--  Students without supervisor starts here -->
    <div class="row">
        <div class="col s12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title green-text">Students without a supervisor</span>
					<div class="collection">
						<?php
							foreach ($noSupervisors as $noSupervisor) {
								echo "<a class='collection-item' href='#'>" . $noSupervisor['student_first'] . " " . $noSupervisor['student_last'] ."</a>";
							}
						?>
					</div>
                </div>
            </div>
        </div>
        <!--  Students without supervisor ends here -->

        <!--  Students without second marker starts here -->
        <div class="row">
            <div class="col s12 l6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title green-text">Students without a second marker</span>
                        <div class="collection">
							<?php
								foreach ($noSecondMarkers as $noSecondMarker) {
									echo "<a class='collection-item' href='#'>". $noSecondMarker['student_first'] . " " . $noSecondMarker['student_last'] . "</a>";
								}
							?>
						</div>
                    </div>
                </div>
            </div>
            <!--  Students without second marker ends here -->

            <!-- end of new -->

        </div>
    </div>


    <!-- Start New Message Modal -->
    <div id="newMessageModal" class="modal modal-fixed-footer">
        <form method="post" action = "messages.php">
            <div class="modal-content">
                <h4>Send a message to supervisor</h4>

                <textarea name="communication_body"></textarea>
                <input type="hidden" name="communication_action" value = "sendmessage" />
                <input type="hidden" name="communication_from_id" value="<?php echo $newCurrentStaff[0]['student_username']; ?>" ?>
                <input type="hidden" name="communication_to_id" value = "<?php echo $supervisor[0]['staff_username']; ?>" />
            </div>
            <div class="modal-footer">

                <button class="waves-effect waves-green btn-flat ">Submit</button>
            </div>
        </form>
    </div>
    <!-- End New Message Modal -->
	<script>
		$(document).ready(function(){
			$('.modal-trigger').leanModal();
		});
	</script>
</body>

</html>