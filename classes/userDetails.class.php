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

    // Get all students
    public function GetAllStudents() {
        $result = $this->con->prepare(
            'SELECT * FROM esuper_student');
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

    //update students last login date
    public function updateLoggedInDate($user_name) {
        $result = $this->con->prepare(
            'Update
              esuper_student
            Set 
              last_login_date = "' . date("Y-m-d H:i:s") . '"
            WHERE
              student_username = :user_name
         ');
        $result->bindValue(':user_name', $user_name);

        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
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

    // Function that returns the allocated students of the specified member of staff
    public function GetAllocatedStudents($staff_username) {
        $result = $this->con->prepare(
            '(
               SELECT
                 s.student_id,
                 s.student_first,
                 s.student_last,
                 s.student_username
               FROM
                 esuper_student s,
                 esuper_staff spv,
                 (
                   SELECT * FROM (SELECT us3.* FROM esuper_user_allocation us3 WHERE us3.supervisor_id IS NOT NULL ORDER BY us3.last_updated DESC) us2 GROUP BY us2.student_id
                 ) spva
               WHERE
                 spva.student_id = s.student_id
               AND
                 spva.supervisor_id = spv.staff_id
               AND
                 spv.staff_username = :staff_username
               ORDER BY
                 s.student_last ASC, s.student_first ASC
             )
               UNION
             (
               SELECT
                 s.student_id,
                 s.student_first,
                 s.student_last,
                 s.student_username
               FROM
                 esuper_student s,
                 esuper_staff snd,
                 (
                   SELECT * FROM (SELECT us3.* FROM esuper_user_allocation us3 WHERE us3.second_id IS NOT NULL ORDER BY us3.last_updated DESC) us2 GROUP BY us2.student_id
                 ) snda
                 WHERE
                   snda.student_id = s.student_id
                 AND
                   snda.second_id = snd.staff_id
                 AND
                   snd.staff_username = :staff_username
                 ORDER BY
                   s.student_last ASC, s.student_first ASC
                 )');
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

    // Function that returns a member of staff
    public function GetStaffByStaffId($staff_id) {
        $result = $this->con->prepare(
            'SELECT
               s.staff_id,
               s.staff_first,
               s.staff_last,
               s.staff_username
             FROM
               esuper_staff s
             WHERE
               s.staff_active = 1
             AND
               s.staff_id = :staff_id');
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

    public function supervisorStudents($staff_id) {
        $result = $this->con->prepare(
            'SELECT
               s.student_id,
               s.student_first,
               s.student_last,
               s.student_username
             FROM
               esuper_student s,
               esuper_staff spv,
               (
                 SELECT * FROM (SELECT us3.* FROM esuper_user_allocation us3 WHERE us3.supervisor_id IS NOT NULL ORDER BY us3.last_updated DESC) us2 GROUP BY us2.student_id
               ) spva
             WHERE
               spva.student_id = s.student_id
             AND
               spva.supervisor_id = spv.staff_id
             AND
               spv.staff_id = :staff_id
             ORDER BY
               s.student_last ASC, s.student_first ASC');
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

    public function noSupervisor() {
        $result = $this->con->prepare(
            'SELECT
               s.student_id,
               s.student_first,
               s.student_last
             FROM
               esuper_student s
             WHERE
               s.student_id NOT IN (
                 SELECT
                   ua.student_id
                 FROM
                   esuper_user_allocation ua
                 WHERE
                   ua.supervisor_id IS NOT NULL
               )');

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
        $result = $this->con->prepare(
            'SELECT
               s.student_id,
               s.student_first,
               s.student_last
             FROM
               esuper_student s
             WHERE
               s.student_id NOT IN (
                 SELECT
                   ua.student_id
                 FROM
                   esuper_user_allocation ua
                 WHERE
                   ua.second_id IS NOT NULL
               )');

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
               t.staff_last,
               t.staff_id,
               t.staff_profile_link,
               t.staff_username
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
               t.staff_last,
               t.staff_id,
               t.staff_profile_link,
               t.staff_username,
               s.student_id
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
             LIMIT 1');
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

    //used to display all unathorised staff in a drop down list on view dashboards
    public function getAllUnauthorisedStaff() {
        $result = $this->con->prepare(
            'SELECT
               *
             FROM
               esuper_staff
             WHERE
               staff_authorised = 0
             ');
        //$result->bindValue(':student_id', $student_id);

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

    //used to display another staffs dashboard
    public function getNewStaffDetails($staff_id) {
        $result = $this->con->prepare(
            'SELECT
               *
             FROM
               esuper_staff
             WHERE
               staff_id = :staff_id
             ');
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

    //used to display another staffs dashboard
    public function getNewStudentDetails($student_id) {
        $result = $this->con->prepare(
            'SELECT
               *
             FROM
               esuper_student
             WHERE
               student_id = :student_id
             ');
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