<?php

session_start();

require '../login-check.php';

$currentUser = $_SESSION['currentUser'];
include '../classes/security.class.php';
include '../classes/communication.class.php';
include '../classes/userDetails.class.php';
include '../classes/errorList.class.php';


$stu_id = $currentUser['student_id']; // (1) = demo student id
$stu_user = $currentUser['student_username']; // (1) = demo student id

$c = new Communication ();
if ($_POST['communication_action']) {
    $el = new errorList ();

    try {
        $c->insert();
    } catch (Exception $e) {
        $el->newList()->type('error')->message($e->getMessage())->go('blogs.php');
        exit;
    }

    $el->newList()->type('success')->message($c->getResponse())->go('blogs.php');
    exit;

}

$el = new errorList ();
if ($el->exists()) {
    echo $el->getResponse();
}


$c->getAll('blog', $stu_user);
$blogs = $c->getResponse();
$blog_count = count($blogs);


$u = new UserDetails ();
$u->studentSuper($stu_id);
$supervisor = $u->getResponse();


?>

<head>
    <title>Messages</title>
    <link href="../css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script>
        function toggleForm(elemID, newButtonID) {
            $(elemID).toggle();
            $(newButtonID).toggle();
        }
        ;

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
                <a href="uploads.php">Uploads</a>
            </li>
        </ul>
        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>

<div class="container">
    <div class="row">


        <!-- BLOG SECTION START -->
        <div id="submitBlog" class="row">
            <i class="small mdi-content-clear c_right-align" onClick="toggleForm('#submitBlog', '#newBlogEntry');"></i>

            <form name="blogEntry" method="post" action='' enctype="multipart/form-data" class="col s10 m12 offset-s1">
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
        <div class="col s10 m12 offset-s1 card">
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
                        echo $b['communication_body'];
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
<script>
    $(document).ready(function () {
        $('.modal-trigger').leanModal();
    });
</script>