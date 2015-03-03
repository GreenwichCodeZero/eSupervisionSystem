<?php

class Comment {

	private $response;
	private $postVars;

	public function getResponse () {
		return $this->response;
	}

	private function response ( $var ) {
		$this->response = $var;
	}

	private function validate() {
        foreach ($_POST as $key => $value) {
            if (empty ($value)) {
                throw new Exception ('Please complete all fields: ' . $key . ' was empty');
                exit;
            }
        }
    }

	function __construct () {
		date_default_timezone_set('Europe/London');


        $s = new Security ();
        $this->postVars = $s->clean($_POST);
        try {
            $this->con = $s->db();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
	}


    public function addComment ( $comment_id, $communication_id) {

        $result = $this->con->prepare (
            'update esuper_communication
            SET
            communication_comment_id = :comment_id
            WHERE
            communication_id = :communication_id
            '
            );

        $result->bindValue(':communication_id', $communication_id);
        $result->bindValue(':comment_id', $comment_id);

        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        $result = null;
        return true;

    }

	public function insert( $staff_user, $student_user ) {



        try {
            $this->validate();
        } catch (Exception $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $result = $this->con->prepare (
        	'INSERT INTO esuper_comment
        	(comment_id, 			
			comment_staff_id,
			comment_body,
			comment_date_added,
			comment_time_added)
        	VALUES
        	(null,
        	:staff_user,
        	:comment_body,
        	:date_added,
        	:time_added
        	)'
        	);

        $result->bindValue(':staff_user', $staff_user);
        $result->bindValue(':comment_body', $this->postVars['comment_body']);
        $result->bindValue(':date_added', date("Y-m-d"));
        $result->bindValue(':time_added', date("H:i:s"));

        try {
            $result->execute();
        } catch (PDOException $e) {
            throw New Exception ($e->getMessage());
            exit;
        }

        $this->comment_id = $this->con->lastInsertId();

        $c = new communication ();


        // Comment submits but communication does not update
        // front end picks up new comment
        // 
        
        try { $this->addComment ($this->comment_id, $this->postVars['comment_communication_id']); }
            
        catch (Exception $e) {
            throw new Exception ( $e->getMessage());
            exit;
        }

  //       print_r ( $this->postVars );
  //       echo "<br>", $this->comment_id;
  //       echo "<br>", $student_user;
  //       echo "<br>", $this->postVars['comment_student_id'];
  //       echo "<br>", $this->postVars['comment_communication_id'];
		// exit;
		// die;

    	$headers = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        if (mail('bd118@greenwich.ac.uk, ' . $student_user . '@greenwich.ac.uk',
            'New Comment Received', 'A new comment was submitted by your tutor and is waiting for you on the eSupervision System.', $headers)) {
            $this->response('Your comment was commited successfully and your student has been notified .');
        } else {
            $this->response('Your comment was commited successfully but your student could not be notified.');
        } 

    }

    public function getComment ( $comment_id ) {

    	$result = $this->con->prepare (
        	'SELECT 
            *
			FROM 
			esuper_comment
			WHERE
			comment_id = :comment_id
        	'
        	);

        $result->bindValue(':comment_id', $comment_id);

        try {
            $result->execute();
        } catch (PDOException $e) {
            throw New Exception ($e->getMessage());
            exit;
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);
        $this->result = null;
        $this->response($row);

    }

    


}

?>