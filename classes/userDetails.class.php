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
			`esuper_staff`.`staff_id`
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

	public function getResponse () {
		return $this->response;
	}

	private function response ($var) {
		return $this->response = $var;
	}

}

?>