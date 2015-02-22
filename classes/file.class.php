<?php
// include ''; // include security class

class File {

// This is the description for the class: File (eSupervision).
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
			$s->clean ($_POST);


		try { $this->con = $s->db (); }

		catch (Exception $e) {
			echo $e->getMessage ();
		}

	}

	public function fileTypes () {

		$result = $this->con->prepare(
			'SELECT 
			`file_type_id`
			,`file_type_name`
			,`file_type_desc`
			,`file_mime_type`
			FROM
			`esuper_file_type`
			'
		);
        $result->execute();

        
        $row = $result->fetchAll();
        $result = null;
        $this->response ($row);

	}



	public function submit ( $user, $file_type_id ) {
	
		// Create database record
		// 
		$fileName = $_FILES['fileToUpload']['name'];
		$tmpName  = $_FILES['fileToUpload']['tmp_name'];
		$fileSize = $_FILES['fileToUpload']['size'];
		$fileType = $_FILES['fileToUpload']['type'];

		// print_r($_FILES);
		$result = $this->con->prepare(
			'SELECT 
			`file_mime_type`
			FROM
			`esuper_file_type`
			WHERE
			`file_type_id` = '.$file_type_id
			
		);
        $result->execute();

        
        $mimeType = $result->fetchAll();

        if ( $_FILES['fileToUpload']['type'] != $mimeType[0]['file_mime_type']) {
        	throw new Exception ("The file you are trying to upload is not the correct file type for this submission.<br>File type allowed: ".$mimeType[0]['file_mime_type']);
        	exit;
        }


		$fp      = fopen($tmpName, 'r');
		$content = fread($fp, filesize($tmpName));
		$content = addslashes($content);
		fclose($fp);

		if(!get_magic_quotes_gpc())
		{
		    $fileName = addslashes($fileName);
		}

		$result = $this->con->prepare(
			"INSERT INTO 
			`esuper_file`
			(file_id, file_owner, file_name, file_size, file_type,  file_content, file_type_id ) 
			VALUES 
			(null, '".$user."', '$fileName', '$fileSize', '$fileType', '$content', '$file_type_id')"
			);
        $result->execute();

        $this->response ('File ['.$fileName.'] successfully submitted');


	}




	// Add a new File to the database
	public function add ( $user ) {

		// Create database record
		// 
		$fileName = $_FILES['fileToUpload']['name'];
		$tmpName  = $_FILES['fileToUpload']['tmp_name'];
		$fileSize = $_FILES['fileToUpload']['size'];
		$fileType = $_FILES['fileToUpload']['type'];

		if 
		($fileSize > 40000000) {
			throw new exception ('File size exceeds the allowed limit: 40MB');
			exit;
		}

		$fp      = fopen($tmpName, 'r');
		$content = fread($fp, filesize($tmpName));
		$content = addslashes($content);
		fclose($fp);

		if(!get_magic_quotes_gpc())
		{
		    $fileName = addslashes($fileName);
		}

		$result = $this->con->prepare(
			"INSERT INTO 
			`esuper_file`
			(file_id, file_owner, file_name, file_size, file_type,  file_content, file_type_id ) 
			VALUES 
			(null, '".$user."', '$fileName', '$fileSize', '$fileType', '$content', 1)"
			);
        $result->execute();

        $this->response ($this->con->lastInsertId());

	}

	private function response ( $var ) {
		$this->response = $var ;
	}

	public function getResponse () {
		return $this->response;
	}
	
	// Find a comment by comment id, type, who posted etc.
	public function getAll ( $user, $type ) { 

		
			$result = $this->con->prepare(
			'SELECT  
			`file_id`, 
			`file_name`
			FROM
			`esuper_file` 
			WHERE 
			`file_owner` = "'.$user.'"');

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

	public function readFile ($file_id) {

         $result = $this->con->prepare(
			'SELECT  
			`file_id`, 
			`file_name`,
			`file_type`,
			`file_size`,
			`file_content`
			FROM
			`esuper_file` 
			WHERE 
			`file_id` = "'.$file_id.'"');

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

	public function get($username, $file_type) {

		switch ($file_type) {
			case "interim":
			$type_id = 5;
			break;
			case "project":
			$type_id = 2;
			break;
			case "ethics":
			$type_id =6;

			break;
			case "initial":
			$type_id = 8;
			break;

			case "proposal":
			$type_id = 3;
			break;

			default: 
			$type_id = 1;
			break;

		}

		 $result = $this->con->prepare(
			"SELECT  
			`file_id`, 
			`file_name`,
			`file_type`,
			`file_size`
			FROM
			`esuper_file` 
			WHERE 
			`file_type_id` = ".$type_id."
			AND
			`file_owner` = '".$username."'
			ORDER BY
			`file_id` DESC
			LIMIT 1");
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

	public function supervisorUploads ($staff, $student) {

		$result = $this->con->prepare(
			'SELECT   
			`esuper_file`.`file_id`,
			`esuper_file`.`file_name`
			FROM
			`esuper_communication`,
			`esuper_file` 
			WHERE 

			`esuper_file`.`file_id` = `esuper_communication`.`communication_file_id`
AND
			`esuper_communication`.`communication_from_id` = "'.$staff.'"
			AND
			`esuper_communication`.`communication_to_id` = "'.$student.'"');

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

}

?>