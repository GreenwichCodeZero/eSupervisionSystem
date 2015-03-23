<?php

require_once 'security.class.php'; // include security class

class Meeting {

    private $response;
    private $con;

    // Load variables from POST into object
    public function __construct() {
        date_default_timezone_set('Europe/London');

        $s = new Security ();
        $s->clean($_POST);
        try {
            $this->con = $s->db();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    // Find a comment by comment id, type, who posted etc.
    public function getAll($username) {
        $result = $this->con->prepare(
            'SELECT
               m.meeting_id,
               m.meeting_date,
               mt.timeslot_time AS meeting_time,
               m.meeting_title,
               m.meeting_content,
               s.meeting_status_name AS meeting_status,
               m.meeting_status_content,
               t.meeting_type_name AS meeting_type,
               st.student_first,
               st.student_last
             FROM
               esuper_meeting m
             JOIN
               esuper_meeting_timeslot mt ON mt.timeslot_id = m.meeting_timeslot_id
             JOIN
               esuper_meeting_status s ON s.meeting_status_id = m.meeting_status_id
             JOIN
               esuper_meeting_type t ON t.meeting_type_id = m.meeting_type_id
             JOIN
               esuper_student st ON st.student_username = m.meeting_student_id
             WHERE
               meeting_student_id = :username
             OR
               meeting_staff_id = :username
             ORDER BY
               m.meeting_date DESC, mt.timeslot_time DESC'
        );
        $result->bindValue(':username', $username);

        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response($row);
    }

    // Find a comment by comment id, type, who posted etc.
    public function getSingle($meetingId) {
        $result = $this->con->prepare(
            'SELECT
               m.meeting_id,
               m.meeting_date,
               mt.timeslot_time AS meeting_time,
               m.meeting_title,
               m.meeting_content,
               s.meeting_status_name AS meeting_status,
               m.meeting_status_content,
               t.meeting_type_name AS meeting_type,
               st.student_first,
               st.student_last
             FROM
               esuper_meeting m
             JOIN
               esuper_meeting_timeslot mt ON mt.timeslot_id = m.meeting_timeslot_id
             JOIN
               esuper_meeting_status s ON s.meeting_status_id = m.meeting_status_id
             JOIN
               esuper_meeting_type t ON t.meeting_type_id = m.meeting_type_id
             JOIN
               esuper_student st ON st.student_username = m.meeting_student_id
             WHERE
               m.meeting_id = :meetingId
             ORDER BY
               m.meeting_id DESC'
        );
        $result->bindValue(':meetingId', $meetingId);

        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response($row);
    }

    public function response($var) {
        $this->response = $var;
    }

    public function getResponse() {
        return $this->response;
    }

}

?>