<?php

class errorList {

	public function __construct () {

		session_start ();	

	}

	// ERROR or SUCCESS
	public function type ( $value ) {
		 $this->type = $value;
		 return $this;
	}


	public function message ( $value ) {
		$this->message .= $value;
		return $this;
	}

	public function newList () {
		$_SESSION['response'] = array ();
		return $this;
	}

	public function closeList () {
		$_SESSION['response']['status'] = 0;
	}

	public function exists () {
		return $_SESSION['response']['status'];
	}

	public function go ($url) {

		$_SESSION['response']['status']= 1;
		$_SESSION['response']['type'] = $this->type;
		$_SESSION['response']['message'] .= $this->message;


		header ('location: '.$url);
		// echo "<script>window.location.href =".$url.";</script>";
	}

	public function getResponse () {
		
		$message = $_SESSION['response']['message'];
		$this->closeList ();
		return $message;
	}

	public function getType () {
		return $_SESSION['response']['type'];
	}


}

?>