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

echo "<pre>";

print_r ($_POST);

echo "</pre>";

$c->getAll('message', $stu_user);
$sent = $c->getResponse();
$sent_count = count($sent);

$c->received('message', $stu_user);
$received = $c->getResponse();
$received_count = count($received);

echo "sent: ";
$sentcount = 0;
foreach ($sent as $b) {
	
	echo "message post:". ++$sentcount;
	echo "<pre>";
		print_r ($b);
	echo "</pre>";
}

echo "<br><br>///////////////////////////////////////////////////////////////////<br><br>";


echo "received: ";
$sentcount = 0;
foreach ($received as $b) {
	
	echo "message post:". ++$sentcount;
	echo "<pre>";
		print_r ($b);
	echo "</pre>";
}
?>