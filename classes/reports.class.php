<?php

class Reports {

    private $response;

    function __construct() {
        $s = new Security ();
        try {
            $this->con = $s->db();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function notActiveButAssignedToASupervisor() {
        $result = $this->con->prepare(
            'SELECT * FROM esuper_student s, esuper_user_allocation ua
            WHERE s.student_id = ua.student_id
            AND s.student_active = 0
               ');

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

    public function notLoggedIn7Days() {
        $result = $this->con->prepare(
            'SELECT
               *
             FROM
               esuper_student
             WHERE
               last_login_date < 7
               ');

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

    public function StaffOver70PercentMeetingsDeclined() {
        $result = $this->con->prepare(
            'SELECT
               s.staff_id,
               s.staff_first,
               s.staff_last,
               s.staff_username
             FROM (
               SELECT
                 all.meeting_staff_id
               FROM
                 (
                   SELECT
                     meeting_staff_id,
                     COUNT(meeting_id) AS `count`
                   FROM
                     esuper_meeting
                   GROUP BY
                     meeting_staff_id
                 ) `all`,
                 (
                   SELECT
                     meeting_staff_id,
                     COUNT(meeting_id) AS `count`
                   FROM
                     esuper_meeting
                   WHERE
                     meeting_status_id = 3
                   GROUP BY
                     meeting_staff_id
                 ) declined
               WHERE
                 all.meeting_staff_id = declined.meeting_staff_id
               AND
                 ((declined.count / all.count) * 100) >= 70
             ) staff
             JOIN
               esuper_staff s ON staff.meeting_staff_id = s.staff_username
             ORDER BY
               s.staff_last ASC, s.staff_first ASC');

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

    public function getResponse() {
        return $this->response;
    }

    private function response($var) {
        return $this->response = $var;
    }

}