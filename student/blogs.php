<?php

session_start();

error_reporting(0);

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/comment.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id

$c = new Communication ();
if ($_POST['communication_action']) {
    $el = new errorList ();

    try {
        $c->insert( $stu_user );
    } catch (Exception $e) {
        $el->newList()->type('error')->message($e->getMessage())->go('blogs.php');
        exit;
    }

    $el->newList()->type('success')->message($c->getResponse())->go('blogs.php');
    exit;

}


$c->getAll('blog', $stu_user, 'student');
$blogs = $c->getResponse();
$blog_count = count($blogs);


$u = new UserDetails ();
$u->getStudentSupervisor($stu_id);
$supervisor = $u->getResponse();

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Blogs</title>
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

<div class="row">
            <?php
                $el = new errorList ();
                if ($el->exists ()){
                    ?>
                    <p class="<?php echo $el->getType (); ?>">
                   <?php echo $el->getResponse (); ?>
                    </p>
                   <?
                }
            ?>
        </div>

        <!-- BLOG SECTION START -->
        <div id="submitBlog" class="row">
            <i class="small mdi-content-clear c_right-align" onClick="toggleForm('#submitBlog', '#newBlogEntry');"></i>

            <form name="blogEntry" method="post" action='' enctype="multipart/form-data" class="col s12">
                <input type='hidden' name='communication_action' value='posttoblog'/>
                <input type='hidden' name='communication_from_id' value='<?php echo $stu_user; ?>'/>
                <input type='hidden' name='communication_to_id' value='blog'/>

                <div class="input-field">
                    <textarea class="materialize-textarea" name='communication_body'></textarea>
                    <label>New Blog Entry</label>
                </div>
                <button class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">Submit
                </button>
            </form>
        </div>
        <div class="col s12 card">
            <a onClick="toggleForm('#submitBlog', '#newBlogEntry');" id="newBlogEntry" class="c_right-align">
                <div class="c_right-align waves-effect waves-teal waves-light green btn-flat white-text">New Entry</div>
            </a>

            <div class="card-content">
                <span class="card-title green-text">Blog History</span>

                <p class="green-text">You have submitted
                    <?php echo $blog_count; ?> Blog posts</p>
                <ul class="collection">
                    <?php foreach ($blogs as $b) {
                        echo '<li class="collection-item">';
                     // Pretty format the date
                         $date = strtotime($b['communication_date_added']);
                         $prettyDate = date('l j F Y', $date);

                         // Output date and time
                         echo "<b> Posted on: ",$prettyDate . ', ' . substr($b['communication_time_added'], 0, -3), "</b><br>"; 
                        echo $b['communication_body'];

                          if ($b['communication_comment_id'] > 0){

                                $cmm1 = new Comment ();
                                $cmm1->getComment ($b['communication_comment_id']);
                                $comment = $cmm1->getResponse ();

                            ?>

                             <!--  COMMENT HTML START -->
							<div class="grey lighten-4">
								<b>
                                <?php $staff =  $u->singleStaff ($comment['comment_staff_id'])->getResponse (); ?>

									Comment from <?php echo $comment_staff = ($comment['comment_staff_id'] == $staff_username) ? "me" :  $staff['staff_first'], ' ', $staff['staff_last']; ?>

									<?php
									// Pretty format the date
										$date = strtotime($comment['comment_date_added']);
										$prettyDate = date('l j F Y', $date);

										// Output date and time
										echo $prettyDate . ', ' . substr($comment['comment_time_added'], 0, -3); 
									?>
								 </b> 
                             </div>
							 <p class="grey lighten-4"><?php echo $comment['comment_body']; ?></p>

                            <!--  COMMENT HTML END -->
                            <? }
                        echo "</li>";
                    } ?>
                </ul>
            </div>
        </div>

        <!-- BLOG SECTION END -->
    </div>

</div>
<!-- end container -->
</body>
</html>