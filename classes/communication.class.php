<?php

require_once 'security.class.php'; // include security class
require_once 'file.class.php'; // include file class

class Communication {

// This is the description for the class: Communication (eSupervision).
// * 
// * @class 	 	Communication
// * @description 	Use to handle all database requests for communications
// * @package    	eSupervision
// * @authors       Dwayne Brown & Mark Tickner 
// * @version    	1.1

	private $action;
	private $response;
	private $con;

	private $communication_id;
	private $from;
	private $to;
	private $date_addded = '';
	private $time_addded ='';
	private $body;
	private $communication_type;

	// Load variables from POST into object 
	public function __construct ( ) {

	    date_default_timezone_set('Europe/London'); 

		$s = new Security (); 
			$s->clean ($_POST);
			try { $this->con = $s->db (); }

			catch (Exception $e) {
				echo $e->getMessage ();
				exit;
			}

	}

	public function insert () {

		try { $this->validate (); }

		catch (Exception $e) {
			throw new Exception ($e->getMessage());
			exit;
		}

		$this->action = ( isset ($_POST['communication_action']) ) ? $_POST['communication_action'] : null;

		switch ( $this->action ) {
			case 'sendmessage':
				echo "message:";
				$this->add ('message');
			break;

			case 'posttoblog':
				echo "Blog Post: id1";
				$this->add ('blog');
			break;

			default: 
				echo "nothing to do <pre>";
				print_r ($_POST);
				exit;
			break;

		}

	}

	public function validate () {

		foreach($_POST as $key => $value) {
 			if (empty ($value)) {
 				throw new Exception ('Please complete all fields');
 				exit;
 			}
		}

	}

	// Add a new communication to the database
	public function add ( $type ) {

		switch ( $type ) {
			case 'message':
				$this->type = 2;
				$this->to = $_POST ['communication_to_id'];
			break;

			case 'blog':
				$this->type = 1;
				$this->to = 0;
			break;

			default: 
				echo "<br>NO POST VALUES<br>";
				print_r ($_POST);
				exit;
			break;

		}


		$this->from = $_POST ['communication_from_id'];
		$this->date_addded = time();
		$this->time_addded = time();
		$this->body = $_POST ['communication_body'];

		$result = $this->con->prepare(
			"INSERT INTO `esuper_communication` (communication_from_id,communication_to_id, communication_date_added, communication_time_added, communication_type_id, communication_body)
			VALUES 
			(:from_id,:to_id,:date_added,:time_added,:type_id ,:comm_body);");

        $result->bindValue(':from_id', $this->from);
        $result->bindValue(':to_id', $this->to);
        $result->bindValue(':date_added', $this->date_addded);
        $result->bindValue(':time_added', $this->date_addded );
        $result->bindValue(':type_id', $this->type);
        $result->bindValue(':comm_body', $this->body);
        try {
        	$result->execute();
        }

        catch (PDOException $e) {
        	echo "ERROR:";
        	echo "\n\n\r\r". $e->getMessage ();
        	exit;
        }

        // $row = $result->fetch(PDO::FETCH_ASSOC);
        $result = null;
        $this->response ('Your content was successfully commited');

     //    echo "trying file <br><br>";

      
     //    if (!$_FILES['fileToUpload']['error']) {
     //    	echo "<pre>";
     //    	print_r ($_FILES);

	    //     $f = new File ();
	    //     try { $f->add (); }
	    //     catch (Exception $e){
	    //     	echo $e->getMessage ();
	    //     	exit;
	    //     }

	    //     echo "<br><br>end test";
    	// }	
	}

	// Find a comment by comment id, type, who posted etc.
	public function getAll ( $type, $user ) { 

		switch ($type) {
			case 'blog': 
				$this->type_id = 1;
			break;

			case 'message':
				$this->type_id = 2;
			break;

		}
			$result = $this->con->prepare(
			'SELECT `communication_body`, `communication_date_added`FROM `esuper_communication` WHERE `communication_type_id` ='.$this->type_id);
        	$result->bindValue(':type_id', 2);
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

	// Remove a communication from the database
	public function remove () {}

	public function response ( $var ) {
		$this->response = $var;
	}

	public function getResponse () {
		return $this->response;
	}

}

?>