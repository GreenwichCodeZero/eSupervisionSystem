<?php
// Database related functions

// Prevent direct access to this file
// Source: http://board.phpbuilder.com/showthread.php?10263469#post10986948
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // File requested directly, redirect user
    header('Location: http://stuiis.cms.gre.ac.uk/codezero/');
} else {
    // Function to create a database connection
    function GetConnection() {
        require 'mysql.php';

        // Return database link
        return mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);
    }

    // Function to close the database connection
    function CloseConnection($link) {
        mysqli_close($link);
    }

    // Function to authenticate a user
    function LoginUser($link, $username, $password) {
        // Get student password hash from database
        $studentResult = mysqli_query($link, "SELECT student_password FROM esuper_student WHERE student_username = '$username'");
        $studentPasswordHash = mysqli_fetch_assoc($studentResult)['student_password'];

        // Get staff password hash from database
        $staffResult = mysqli_query($link, "SELECT staff_password FROM esuper_staff WHERE staff_username = '$username'");
        $staffPasswordHash = mysqli_fetch_assoc($staffResult)['staff_password'];

        if (mysqli_num_rows($studentResult) == 1) {
            // User found in student table
            if (password_verify($password, $studentPasswordHash)) {
                $sql = "SELECT student_id, student_first, student_last, student_username, student_banner_id, student_active AS is_active FROM esuper_student WHERE student_username = '$username'";
                $result = mysqli_query($link, $sql);
                $student = mysqli_fetch_assoc($result);
                $student += array('user_type' => 'student');

                return $student;
            }
        } else if (mysqli_num_rows($staffResult) == 1) {
            // User found in staff table
            if (password_verify($password, $staffPasswordHash)) {
                $sql = "SELECT staff_id, staff_first, staff_last, staff_username, staff_banner_id, staff_active AS is_active, staff_authorised FROM esuper_staff WHERE staff_username = '$username'";
                $result = mysqli_query($link, $sql);
                $staff = mysqli_fetch_assoc($result);
                $staff += array('user_type' => 'staff');

                return $staff;
            }
        } else {
            return null;
        }
    }

    // Function that inserts a meeting into the database
    function InsertMeeting($link, $date, $title, $content, $type, $student_id, $staff_id) {
        $sql = "INSERT
                  INTO  esuper_meeting (meeting_date, meeting_title, meeting_content, meeting_type_id, meeting_student_id, meeting_staff_id)
                  VALUES ('$date', '$title', '$content', $type, '$student_id', '$staff_id')";

        return mysqli_query($link, $sql);
    }
}