<?php

session_start();

require '../login-check.php';
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';

$currentUser = $_SESSION['currentUser'];
$stu_id = $currentUser['student_id']; // 
$stu_user = $currentUser['student_username']; //

$c = new Communication ();
if ($_POST['communication_action']) {
    $el = new errorList ();

    try {
        $c->insert($stu_user);
    } catch (Exception $e) {

        $el->newList()->type('error')->message($e->getMessage())->go('messages.php');
        exit;
    }

    $el->newList()->type('success')->message($c->getResponse())->go('messages.php');
    exit;
}

$u = new UserDetails ();
$u->getStudentSupervisor($stu_id);
$supervisor = $u->getResponse();

$c->getAll('message', $stu_user, 'student', $supervisor[0]['staff_username']);

$sent = $c->getResponse();
$message_count = count($sent);

$c->received($stu_user, 'student');
$received = $c->getResponse();
$received_count = count($received);

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
        }

        $(document).ready(function () {
            $(".button-collapse").sideNav();
        });

        // Client-side form validation
        // Function to display any error messages on form submit
        /**
         * @return {boolean}
         */
        function ValidateForm() {
            var isValid = true;

            // Validate message
            if (ValidateMessage(document.getElementById('message').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the message
        function ValidateMessage(content) {
            var output;
            if (/^\s*$/.test(content)) {
                output = 'Enter a message';
            } else {
                output = '';
            }

            document.getElementById('messageValidation').innerHTML = output;
            return output;
        }
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
        <div>
            <?php
            $el = new errorList ();
            if ($el->exists()) {
                ?>
                <p class='<?php echo $el->getType(); ?>'>
                    <?php echo $el->getResponse(); ?>
                </p>
            <? } ?>
        </div>

        <div id="sendMessage" class="card col s12">
            <i class="small mdi-content-clear c_right-align" onClick="toggleForm('#sendMessage', '#newMessage');"></i>

            <div class="card-content">
                <form id='communication' action='' method='POST' enctype="multipart/form-data">
                    <input type='hidden' name='communication_action' value='sendmessage'/>

                    <input type="hidden" name="communication_from_id"
                           value="<?php echo $currentUser['student_username']; ?>" ?>
                    <input type="hidden" name="communication_to_id"
                           value="<?php echo $supervisor[0]['staff_username']; ?>"/>
                    <input type='hidden' name='communication_type_id' value='2'/>

                    <div class="input-field">
                        <label for="message">New Message</label>
                        <textarea id="message" class="materialize-textarea" name='communication_body'
                                  onkeyup="ValidateMessage(this.value);"></textarea>
                        <span id="messageValidation" class="red-text text-light-3 validation-error"></span>
                    </div>
                    <input class="waves-effect waves-teal waves-light btn-flat" type="file" name="fileToUpload"
                           id="fileToUpload">
                    <button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text"
                            onclick="return ValidateForm()">Submit
                    </button>
                </form>
            </div>
        </div>
        <div class="col s12 card">
            <a onClick="toggleForm('#sendMessage', '#newMessage');" class="c_right_align" id="newMessage">
                <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">New Message
                </div>
            </a>

            <div class="card-content">
                <span class="card-title green-text">Message History</span>

                <p class="green-text"><?php echo '<p>There are ' . $message_count . ' messages between you and ' . $supervisor[0]['staff_first'] . ' ' . $supervisor[0]['staff_last'] . '.</p>'; ?>

                <ul class="collection">

                    <?php

                    if ($message_count > 0) {
                        foreach ($sent as $s) { ?>
                            <li class="collection-item" <?php echo ($s['communication_from_id'] == $stu_user) ? 'style="background-color: #fafafa;"' : '' ?> >
                                <form action="readfile.php" method="POST">
                                    <p>
                                        <span class="green-text">
                                            <b>
                                                <?php if ($s['communication_from_id'] == $stu_user) {
                                                    // Message is from current student user
                                                    echo 'Me';
                                                } else {
                                                    // Message is from staff
                                                    echo $s['staff_first'] . " " . $s['staff_last'];
                                                } ?>
                                            </b>
                                        </span>
                                        &#8212;

                                        <?php
                                        // Pretty format the date
                                        $date = strtotime($s['communication_date_added']);
                                        $prettyDate = date('l j F Y', $date);

                                        // Output date and time
                                        echo $prettyDate . ', ' . substr($s['communication_time_added'], 0, -3); ?>
                                    </p>

                                    <p>
                                        <?php echo $s['communication_body']; ?>
                                    </p>


                                 

                                    <?php
                                    if ($s['communication_file_id'] > 0) { ?>
                                        <hr/>
                                        <p>
                                            <input type='hidden' name='file_id' value="<?php echo $s['communication_file_id']; ?>"/>
                                            <button
                                                class="waves-effect waves-teal waves-light green btn-flat white-text"
                                                style="margin-bottom: 0; margin-top: 15px;">
                                                View File<i class="mdi-editor-attach-file right"></i></button>
                                        </p>
                                    <?php } ?>
                                </form>
                            </li>
                        <?php }  // END FOREACH

                    } else {// End If ?>

                        <li class="collection-item">There are no messages to display.</li>
                    <?php } ?>

                </ul>
            </div>
        </div>
        <!--MESSAGING SECTION END-->
    </div>
</div>
<!-- end container -->
</body>
<script>
$('#communication').submit(function(){
    $('button').remove ();
});
</script>
</html>