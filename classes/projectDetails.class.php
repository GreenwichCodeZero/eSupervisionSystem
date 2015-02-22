<?php
// include ''; // include security class

class projectDetails {

// This is the description for the class: projectDetails (eSupervision).
// * 
// * @class 	 	File
// * @description 	Use to handle all database requests for Files
// * @package    	eSupervision
// * @authors       Dwayne Brown & Mark Tickner 
// * @version    	1.1

	private $response;

	// Load variable from POST into object 
	public function __construct () {

		$s = new Security (); 
			$this->postVars = $s->clean ($_POST);

		try { $this->con = $s->db (); }

		catch (Exception $e) {
			echo $e->getMessage ();
		}

	}

	public function validate () {

		foreach($this->postVars as $key => $value) {
 			if (empty ($value)) {
 				throw new Exception ('Please complete all fields: '.$key.' was empty');
 				exit;
 			}
		}

	}

	public function studentProject ( $student ) {

		$result = $this->con->prepare(
			'SELECT 
			`project_title`
			FROM
			`esuper_project`
			WHERE
			`student_username` = "'.$student.'"
			ORDER BY 
			`project_id` DESC
			LIMIT 1
			'
		);

        try { $result->execute(); }
         catch (PDOException $e) {
        	throw new Exception ( "\n\n\r\r". $e->getMessage ());
        	exit;
        }

        
        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);

	}

	public function newTitle ($student) {

		try { $this->validate (); }

		catch (Exception $e) {
			throw new Exception ($e->getMessage());
			exit;
		}

		$result = $this->con->prepare(
			"INSERT INTO 
			`esuper_project`
			(project_id, project_title, student_username ) 
			VALUES 
			(null, '".$this->postVars['title']."', '".$student."')"
		);

        try { $result->execute(); }

        catch (PDOException $e) {
        	throw new Exception ( "\n\n\r\r". $e->getMessage ());
        	exit;
        }
        
        $result = null;
        $this->response ("Your Project Title was successfully updated");
	}

	private function response ($var) {

		$this->response = $var;
	}

	public function getResponse () {
		return $this->response; 
	}



}

?>