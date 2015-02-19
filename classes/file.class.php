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

<<<<<<< HEAD
	// Load variables from POST into object 
=======
	private $response;

	// Load variable from POST into object 
>>>>>>> branchFeature-US18
	public function __construct () {

		$s = new Security (); 
			$s->clean ($_POST);

<<<<<<< HEAD
	}


	// Add a new File to the database
	public function test () {
	
		print_r($_FILES);

	}

	// Add a new File to the database
	public function add () {

		// Move file to upload folder
		try { $this->upload (); }

		catch (Exception $e) {
			throw new Exception ($e->getMessage ());
		}

		// // Create database record
		// $s = new Security ();

		// try { $this->con = $s->db (); }

		// catch (Exception $e) {
		// 	echo $e->getMessage ();
		// }

		// $result = $this->con->prepare("INSERT INTO ``  () VALUES (: ,: )");
  //       $result->bindValue(': ', $user);
  //       $result->execute();

  //       $row = $result->fetch(PDO::FETCH_ASSOC);

	}

	private function upload () {

		if($_FILES['file_upload']['error'] > 0){
   			throw new Exception('An error ocurred when uploading.');
		}

		// Check filesize
		if($_FILES['file_upload']['size'] > 500000){
		    throw new Exception('File uploaded exceeds maximum upload size.');
		}

		// Check if the file exists
		if(file_exists('upload/' . $_FILES['file_upload']['name'])){
		    throw new Exception('File with that name already exists.');
		}

		// Upload file
		if(!move_uploaded_file($_FILES['file_upload']['tmp_name'], 'upload/' . $_FILES['file_upload']['name'])){
		    throw new Exception('Error uploading file - check destination is writeable.');
		}

		throw new Exception('File uploaded successfully.');

		// throw new Exception ("No file included in request");

	}

	// // Find a File by File id, person etc.
	// public function get () { 

	// 	$s = new Security ();

	// 	try { $this->con = $s->db (); }
		
	// 	catch (Exception $e) {
	// 		echo $e->getMessage ();
	// 	}

	// 	$result = $this->con->prepare("SELECT ``  FROM `` WHERE  `` = :  ");
 //        $result->bindValue(': ', $user);
 //        $result->execute();

 //        $row = $result->fetch(PDO::FETCH_ASSOC);

	// }
=======

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

		$fp      = fopen($tmpName, 'r');
		$content = fread($fp, filesize($tmpName));
		$content = addslashes($content);
		fclose($fp);

		if(!get_magic_quotes_gpc())
		{
		    $fileName = addslashes($fileName);
		}

		echo "preparing";
		$result = $this->con->prepare(
			"INSERT INTO 
			`esuper_file`
			(file_id, file_owner, file_name, file_type, file_size, file_content ) 
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

>>>>>>> branchFeature-US18

}

?>