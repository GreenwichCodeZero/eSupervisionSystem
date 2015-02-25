<?php
if(isset($_GET['searchSubmit'])) {

$name = $_GET['searchByName'];

$noStudentsFound = "No students found";
}

if(isset($_GET['searchProgrammeSubmit'])) {

$programmeID = $_GET['searchProgramme'];

$noProgrameStudents = "No students found on this programme";
}