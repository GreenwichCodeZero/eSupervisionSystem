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


	// Add a new File to the database
	public function add ( $user ) {

	
		// Create database record
		// 
		$fileName = $_FILES['fileToUpload']['name'];
		$tmpName  = $_FILES['fileToUpload']['tmp_name'];
		$fileSize = $_FILES['fileToUpload']['size'];
		$fileType = $_FILES['fileToUpload']['type'];

		// echo "<pre>";
		// 	print_r ($_FILES);
		// echo "</pre>";
		// exit;
		// die; 
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
			(file_id, file_owner, file_name, file_size, file_type,  file_content ) 
			VALUES 
			(null, '".$user."', '$fileName', '$fileSize', '$fileType', '$content')"
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
	public function getAll ( $user ) { 

		switch ($type) {
			case 'blog': 
				$this->type_id = 1;
			break;

			case 'message':
				$this->type_id = 2;
			break;

		}
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


}

?>