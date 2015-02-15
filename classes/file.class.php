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

	// Load variables from POST into object 
	public function __construct () {

		$s = new Security (); 
			$s->clean ($_POST);

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

}

?>