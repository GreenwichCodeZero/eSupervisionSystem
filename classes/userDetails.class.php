<?php

class UserDetails {
	
	private $response;

	function __construct () {
		$s = new Security (); 
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

	}

	public function studentSuper ($student_id) {
		
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

	public function AllMyStudents ( $staff_username ) {
			$result = $this->con->prepare(
			'SELECT 
			`esuper_student`.`student_first`,
			`esuper_student`.`student_last`,
			`esuper_student`.`student_username`,
			FROM 
			`esuper_students`,
			`esuper_user_allocation`,
			`esuper_staff`
			WHERE
			`esuper_user_allocation`.`supervisor_id` = `esuper_staff`.`staff_id`
			AND
			`esuper_staff`.`staff_username` = "'.$staff_username.'"');
        
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

	public function getResponse () {
		return $this->response;
	}

	private function response ($var) {
		return $this->response = $var;
	}

}

?>