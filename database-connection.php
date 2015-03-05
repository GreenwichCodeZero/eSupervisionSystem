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

    // Function that returns timeslots by staff username
    function GetStaffTimeslots($link, $staffUsername) {
        //todo make sure timeslot is available and not already taken
        $sql = "SELECT mt.timeslot_id, mt.timeslot_day, mt.timeslot_time
                FROM esuper_meeting_timeslot mt
                JOIN esuper_staff s ON mt.staff_id = s.staff_id
                WHERE s.staff_username = '$staffUsername'
                AND mt.timeslot_active = 1
                ORDER BY FIELD(mt.timeslot_day, 'M', 'TU', 'W', 'TH', 'F') ASC, mt.timeslot_time ASC";
        $result = mysqli_query($link, $sql);

        $timeslots = array();

        // Add each timeslot to an array
        while ($timeslot = mysqli_fetch_assoc($result)) {
            array_push($timeslots, $timeslot);
        }

        return $timeslots;
    }

    // Function that checks the availability of a meeting timeslot on the specified date
    function CheckTimeslotAvailability($link, $timeslotId, $timeslotDate) {
        $sql = "SELECT meeting_id FROM esuper_meeting WHERE meeting_date = '$timeslotDate' AND meeting_timeslot_id = $timeslotId";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) >= 1) {
            // False means timeslot is not available
            return false;
        } else {
            // True means timeslot is available
            return true;
        }
    }

    // Function that inserts a meeting into the database
    function InsertMeeting($link, $timeslotId, $meetingDate, $title, $content, $type, $studentUsername, $staffUsername) {
        $sql = "INSERT INTO  esuper_meeting (meeting_date, meeting_timeslot_id, meeting_title, meeting_content, meeting_type_id, meeting_student_id, meeting_staff_id)
                VALUES ('$meetingDate', $timeslotId, '$title', '$content', $type, '$studentUsername', '$staffUsername')";

        return mysqli_query($link, $sql);
    }

    // Function that updates the specified meetings status and the returns the students username
    function UpdateMeetingStatus($link, $meetingId, $newStatus) {
        $sql = "UPDATE esuper_meeting SET meeting_status_id = $newStatus WHERE meeting_id = $meetingId";
        mysqli_query($link, $sql);

        $sql = "SELECT meeting_student_id FROM esuper_meeting WHERE meeting_id = $meetingId";
        $result = mysqli_query($link, $sql);
        $studentUsername = mysqli_fetch_assoc($result);

        return $studentUsername['meeting_student_id'];
    }

    // Function that updates the specified meetings content record
    function UpdateRecordMeeting($link, $meetingId, $contentRecord) {
        $sql = "UPDATE esuper_meeting SET meeting_status_content = '$contentRecord' WHERE meeting_id = $meetingId";

        return mysqli_query($link, $sql);
    }

    // Function that inserts a supervisor allocation into the database
    function AllocateStudentSupervisor($link, $studentId, $supervisorId, $updatedByStaffId) {
        $sql = "INSERT INTO esuper_user_allocation (student_id, supervisor_id, updated_by)
                VALUES ($studentId, $supervisorId, $updatedByStaffId)";

        return mysqli_query($link, $sql);
    }

    // Function that inserts a second marker allocation into the database
    function AllocateStudentSecondMarker($link, $studentId, $secondMarkerId, $updatedByStaffId) {
        $sql = "INSERT INTO esuper_user_allocation (student_id, second_id, updated_by)
                VALUES ($studentId, $secondMarkerId, $updatedByStaffId)";

        return mysqli_query($link, $sql);
    }
}