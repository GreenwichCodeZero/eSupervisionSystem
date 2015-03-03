<?php

class UserDetails {

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

    // Get student details by username (including programme ID)
    public function GetStudentDetails($student_username) {
        $result = $this->con->prepare(
            'SELECT
			   s.student_id,
			   s.student_first,
			   s.student_last,
			   s.student_banner_id,
			   p.programme_id,
			   p.programme_title
			 FROM
			   esuper_student s,
               esuper_programme p,
               (SELECT * FROM (
                   SELECT us3.* FROM esuper_user_programme us3 ORDER BY us3.last_updated DESC
                 ) us2
               GROUP BY us2.student_id) us
			 WHERE
			   s.student_username = :student_username
             AND
               p.programme_id = us.programme_id
             AND
               us.student_id = s.student_id
             AND
               s.student_active = 1
             ORDER BY
               s.student_last ASC, s.student_first ASC');
        $result->bindValue(':student_username', $student_username);

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

    public function studentSuper($student_id) {
        $result = $this->con->prepare(
            'SELECT
			`esuper_staff`.`staff_first`,
			`esuper_staff`.`staff_last`,
			`esuper_staff`.`staff_id`,
			`esuper_staff`.`staff_profile_link`,
			`esuper_staff`.`staff_username`

			FROM 
			`esuper_user_allocation`, 
			`esuper_staff`
			WHERE
			`esuper_user_allocation`.`student_id` = :student_id
			AND 
			`esuper_user_allocation`.`supervisor_id` = `esuper_staff`.`staff_id`');

        $result->bindValue(':student_id', $student_id);
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

    public function AllMyStudents($staff_id) {
        $result = $this->con->prepare(
            'SELECT student.student_first, student.student_last, student.student_username
             FROM esuper_student student
             JOIN esuper_user_allocation allocation ON student.student_id = allocation.student_id
             JOIN esuper_staff spv ON allocation.supervisor_id = spv.staff_id
             JOIN esuper_staff snd ON allocation.second_id = snd.staff_id
             WHERE spv.staff_id = :staff_id
             OR snd.staff_id = :staff_id
             ORDER BY student.student_last');
        $result->bindValue(':staff_id', $staff_id);

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

    // Function that returns the allocated students of the specified member of staff
    public function GetAllocatedStudents($staff_username) {
        $result = $this->con->prepare(
            'SELECT student.student_first, student.student_last, student.student_username
             FROM esuper_student student
             JOIN esuper_user_allocation allocation ON student.student_id = allocation.student_id
             JOIN esuper_staff spv ON allocation.supervisor_id = spv.staff_id
             JOIN esuper_staff snd ON allocation.second_id = snd.staff_id
             WHERE spv.staff_username = :staff_username
             OR snd.staff_username = :staff_username
             ORDER BY student.student_last');
        $result->bindValue(':staff_username', $staff_username);

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

    // Function that returns all active staff
    public function GetAllStaff() {
        $result = $this->con->prepare(
            'SELECT
               s.staff_id,
               s.staff_first,
               s.staff_last,
               s.staff_username,
               p.programme_id,
               p.programme_title
             FROM
               esuper_staff s,
               esuper_programme p,
               (SELECT * FROM (
                   SELECT us3.* FROM esuper_user_programme us3 ORDER BY us3.last_updated DESC
                 ) us2
               GROUP BY us2.staff_id) us
             WHERE
               p.programme_id = us.programme_id
             AND
               us.staff_id = s.staff_id
             AND
               s.staff_active = 1
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

    // Function that returns all active staff of a specified programme
    public function GetStaffByProgrammeId($programme_id) {
        $result = $this->con->prepare(
            'SELECT
               s.staff_id,
               s.staff_first,
               s.staff_last,
               s.staff_username,
               p.programme_id,
               p.programme_title
             FROM
               esuper_staff s,
               esuper_programme p,
               (SELECT * FROM (
                   SELECT us3.* FROM esuper_user_programme us3 ORDER BY us3.last_updated DESC
                 ) us2
               GROUP BY us2.staff_id) us
             WHERE
               p.programme_id = :programme_id
             AND
               p.programme_id = us.programme_id
             AND
               us.staff_id = s.staff_id
             AND
               s.staff_active = 1
             ORDER BY
               s.staff_last ASC, s.staff_first ASC');
        $result->bindValue(':programme_id', $programme_id);

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

    public function studentSM($student_id) {
        $result = $this->con->prepare(
            'SELECT
			`esuper_staff`.`staff_first`,
			`esuper_staff`.`staff_last`,
			`esuper_staff`.`staff_id`,
			`esuper_staff`.`staff_profile_link`

			FROM 
			`esuper_user_allocation`, 
			`esuper_staff`
			WHERE
			`esuper_user_allocation`.`student_id` = :student_id
			AND 
			`esuper_user_allocation`.`second_id` = `esuper_staff`.`staff_id`');

        $result->bindValue(':student_id', $student_id);
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

    public function supervisorStudents($user_id) {
        // $s = new Security (); 
        //     try { $this->con = $s->db (); }

        //     catch (Exception $e) {
        //         echo $e->getMessage ();
        //         exit;
        //     }

        $result = $this->con->prepare(
            'SELECT * FROM esuper_user_allocation, esuper_staff, esuper_student WHERE esuper_user_allocation.supervisor_id = ' . $user_id . ' AND esuper_user_allocation.supervisor_id = esuper_staff.staff_id AND  esuper_user_allocation.student_id = esuper_student.student_id');
        $result->bindValue(':student_id', $student_id);
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

    public function noSupervisor() {
        // $s = new Security (); 
        //     try { $this->con = $s->db (); }

        //     catch (Exception $e) {
        //         echo $e->getMessage ();
        //         exit;
        //     }

        $result = $this->con->prepare(
            'SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last from esuper_student WHERE esuper_student.student_id NOT IN (SELECT student_id from esuper_user_allocation) UNION SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last FROM esuper_user_allocation, esuper_student WHERE esuper_user_allocation.student_id = esuper_student.student_id AND esuper_user_allocation.supervisor_id IS NULL ');
        // $result->bindValue();
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

    public function noSecondMarker() {
        // $s = new Security (); 
        //     try { $this->con = $s->db (); }

        //     catch (Exception $e) {
        //         echo $e->getMessage ();
        //         exit;
        //     }

        $result = $this->con->prepare(
            'SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last from esuper_student WHERE esuper_student.student_id NOT IN (SELECT student_id from esuper_user_allocation) UNION SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last FROM esuper_user_allocation, esuper_student WHERE esuper_user_allocation.student_id = esuper_student.student_id AND esuper_user_allocation.second_id IS NULL ');
        //      $result->bindValue();
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

    public function isStaffAuthorised($staff_id) {
        $result = $this->con->prepare("SELECT staff_authorised FROM esuper_staff WHERE staff_id = " . '"' . $staff_id . '"');

        $result->bindValue(':staff_id', $staff_id);
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

    public function searchStudents($student_name) {
        $result = $this->con->prepare(
            'SELECT
			   s.student_id,
			   s.student_first,
			   s.student_last,
               s.student_username,
			   s.student_banner_id,
			   p.programme_id,
			   p.programme_title
			 FROM
			   esuper_student s,
               esuper_programme p,
               (SELECT * FROM (
                   SELECT us3.* FROM esuper_user_programme us3 ORDER BY us3.last_updated DESC
                 ) us2
               GROUP BY us2.student_id) us
			 WHERE
               (s.student_first LIKE :student_name OR s.student_last LIKE :student_name OR CONCAT(s.student_first, " ", s.student_last) LIKE :student_name)
             AND
               p.programme_id = us.programme_id
             AND
               us.student_id = s.student_id
             AND
               s.student_active = 1
             ORDER BY
               s.student_last ASC, s.student_first ASC'
        );
        $result->bindValue(':student_name', $student_name . '%');

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

    public function searchStudentsByProgramme($programme_id) {
        $result = $this->con->prepare(
            'SELECT
               s.student_id,
               s.student_first,
               s.student_last,
               s.student_username,
               s.student_banner_id,
               p.programme_id,
               p.programme_title
             FROM
               esuper_student s,
               esuper_programme p,
               (SELECT * FROM (
                   SELECT us3.* FROM esuper_user_programme us3 ORDER BY us3.last_updated DESC
                 ) us2
               GROUP BY us2.student_id) us
             WHERE
               p.programme_id = :programme_id
             AND
               p.programme_id = us.programme_id
             AND
               us.student_id = s.student_id
             AND
               s.student_active = 1
             ORDER BY
               s.student_last ASC, s.student_first ASC'
        );
        $result->bindValue(':programme_id', $programme_id);

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

    public function getStudentSupervisor($student_id) {
        $result = $this->con->prepare(
            'SELECT
               t.staff_first,
               t.staff_last
             FROM
               esuper_student s,
               esuper_staff t,
               esuper_user_allocation a
             WHERE
               s.student_id = :student_id
             AND
               s.student_id = a.student_id
             AND
               t.staff_id = a.supervisor_id
             ORDER BY
               a.last_updated DESC
             LIMIT 1'
        );
        $result->bindValue(':student_id', $student_id);

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

    public function getStudentSecondMarker($student_id) {
        $result = $this->con->prepare(
            'SELECT
               t.staff_first,
               t.staff_last
             FROM
               esuper_student s,
               esuper_staff t,
               esuper_user_allocation a
             WHERE
               s.student_id = :student_id
             AND
               s.student_id = a.student_id
             AND
               t.staff_id = a.second_id
             ORDER BY
               a.last_updated DESC
             LIMIT 1'
        );
        $result->bindValue(':student_id', $student_id);

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

?>