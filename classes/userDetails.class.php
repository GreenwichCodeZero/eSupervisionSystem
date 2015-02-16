<?php

class UserDetails {
	
	private $response;

	public function studentSuper ($student_id) {
		$s = new Security (); 
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

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
			`esuper_user_allocation`.`supervisor_id` = `esuper_staff`.`staff_id`');
        
        $result->bindValue(':student_id', $student_id);
        try {
        	$result->execute();
        }

        catch (PDOException $e) {
        	echo "ERROR:";
        	echo "\n\n\r\r". $e->getMessage ();
        	exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);
	}

	public function studentSM ($student_id) {
		$s = new Security (); 
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

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
        }

        catch (PDOException $e) {
        	echo "ERROR:";
        	echo "\n\n\r\r". $e->getMessage ();
        	exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);
	}


	public function supervisorStudents ($staff_id) {
		$s = new Security (); 
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

		$result = $this->con->prepare(
			'SELECT * FROM esuper_user_allocation, esuper_staff, esuper_student WHERE esuper_user_allocation.supervisor_id = ' . $staff_id . ' AND esuper_user_allocation.supervisor_id = esuper_staff.staff_id AND  esuper_user_allocation.student_id = esuper_student.student_id');
        $result->bindValue(':student_id', $student_id);
        try {
        	$result->execute();
        }

        catch (PDOException $e) {
        	echo "ERROR:";
        	echo "\n\n\r\r". $e->getMessage ();
        	exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);
	}

		public function noSupervisor () {
		$s = new Security (); 
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

		$result = $this->con->prepare(
'SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last from esuper_student WHERE esuper_student.student_id NOT IN (SELECT student_id from esuper_user_allocation) UNION SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last FROM esuper_user_allocation, esuper_student WHERE esuper_user_allocation.student_id = esuper_student.student_id AND esuper_user_allocation.supervisor_id IS NULL ');
       // $result->bindValue();
        try {
        	$result->execute();
        }

        catch (PDOException $e) {
        	echo "ERROR:";
        	echo "\n\n\r\r". $e->getMessage ();
        	exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);
	}


	public function noSecondMarker () {
		$s = new Security (); 
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

		$result = $this->con->prepare(
'SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last from esuper_student WHERE esuper_student.student_id NOT IN (SELECT student_id from esuper_user_allocation) UNION SELECT esuper_student.student_id, esuper_student.student_first, esuper_student.student_last FROM esuper_user_allocation, esuper_student WHERE esuper_user_allocation.student_id = esuper_student.student_id AND esuper_user_allocation.second_id IS NULL ');
  //      $result->bindValue();
        try {
        	$result->execute();
        }

        catch (PDOException $e) {
        	echo "ERROR:";
        	echo "\n\n\r\r". $e->getMessage ();
        	exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);
	}

	public function getResponse () {
		return $this->response;
	}

	private function response ($var) {
		return $this->response = $var;
	}

}

?>