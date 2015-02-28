<?php
if(isset($_GET['searchSubmit'])) {

$name = $_GET['searchByName'];
$submit = 1;

if($name == null){
	$submit = 0;
$noStudentsFound = 'Please enter a students name';
}else{
	$noStudentsFound = 'No students found by the name "' . $name . '"';

}
}

if(isset($_GET['searchProgrammeSubmit'])) {

$programmeID = $_GET['searchProgramme'];

$noProgrameStudents = "No students found on this programme";
}