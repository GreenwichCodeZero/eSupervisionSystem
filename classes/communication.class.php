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
    private $from;
    private $to;
    private $body;

    // Load variables from POST into object
    public function __construct() {
        date_default_timezone_set('Europe/London');

        $s = new Security ();
        $_POST = $s->clean($_POST);
        try {
            $this->con = $s->db();
        } catch (Exception $e) {
            throw new Exception ($e->getMessage());
            exit;
        }
    }

    public function insert($from_user, $file_type_id = null) {
        try {
            $this->validate();
        } catch (Exception $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $this->from = $from_user;
        $this->action = (isset ($_POST['communication_action'])) ? $_POST['communication_action'] : null;

        switch ($this->action) {
            case 'sendmessage':
                $this->add('message', $file_type_id);
                break;

            case 'posttoblog':
                $this->add('blog', $file_type_id);
                break;

            default:
                echo "insert:<pre>";
                print_r($_POST);
                exit;
                break;
        }
    }

    public function validate() {
        foreach ($_POST as $key => $value) {
            if (empty ($value)) {
                throw new Exception ('Please complete all fields: ' . $key . ' was empty');
                exit;
            }
        }
    }

    // Add a new communication to the database
    public function add($type, $file_type_id = null) {
        switch ($type) {
            case 'message':
                $this->type = 2;
                $this->to = $_POST ['communication_to_id'];
                break;

            case 'blog':
                $this->type = 1;
                $this->to = 0;
                break;

            default:
                echo "<br>add: <br>";
                print_r($_POST);
                exit;
                break;
        }

        $this->file_id = null;

        if ($_FILES['fileToUpload']['size'] > 0) {

            $f = new File ();
            try {
                $f->add($this->from, $type_id = ($file_type_id > 0) ? $file_type_id : 1);
            } catch (Exception $e) {
                throw new Exception ($e->getMessage());
                exit;
            }

            $this->file_id = $f->getResponse();
            $this->response('file uploaded: ' . $this->file_id);
        }

        $this->from = strip_tags($_POST ['communication_from_id']);
        $this->to = strip_tags($_POST ['communication_to_id']);
        $this->body = strip_tags($_POST ['communication_body']);

        $result = $this->con->prepare(
            "INSERT INTO
               esuper_communication (
                 communication_from_id,
                 communication_to_id,
                 communication_date_added,
                 communication_time_added,
                 communication_type_id,
                 communication_body,
                 communication_file_id
               )
			VALUES (
			  :from_id,
			  :to_id,
			  :date_added,
			  :time_added,
			  :type_id,
			  :comm_body,
			  :comm_file_id
			)"
        );
        $result->bindValue(':from_id', $this->from);
        $result->bindValue(':to_id', $this->to);
        $result->bindValue(':date_added', date("Y-m-d"));
        $result->bindValue(':time_added', date("H:i:s"));
        $result->bindValue(':type_id', $this->type);
        $result->bindValue(':comm_body', $this->body);
        $result->bindValue(':comm_file_id', $this->file_id);

        try {
            $result->execute();
        } catch (PDOException $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $result = null;

        $headers = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        if (mail($this->from . '@greenwich.ac.uk',
            'New Message Received', 'A new message was submitted and is waiting for you on the eSupervision System.', $headers)) {
            $this->response('Your content was commited successfully and a notification email has been sent .');
        } else {
            $this->response('Your content was commited successfully but a notification email could not be sent.');
        }
    }

    // Find a comment by comment id, type, who posted etc.
    public function getAll($type, $user, $user_type = null, $filter_username = null) {
        switch ($type) {
            case 'blog':
                switch ($user_type) {
                    case 'staff';
                        $sql = 'SELECT
                                  c.communication_id,
                                  c.communication_body,
                                  c.communication_date_added,
                                  c.communication_time_added,
                                  c.communication_file_id,
                                  s.student_first,
                                  s.student_last,
                                  c.communication_from_id,
                                  c.communication_to_id,
                                  c.communication_comment_id
                                FROM
                                  esuper_communication c,
                                  esuper_student s
                                WHERE
                                  c.communication_type_id = 1
                                AND
                                  (s.student_username = c.communication_from_id OR s.student_username = c.communication_to_id)';

                        // Add filter if necessary
                        if ($filter_username != null) {
                            $sql .= ' AND c.communication_from_id = :student_username';
                        }

                        $sql .= ' ORDER BY c.communication_date_added DESC, c.communication_time_added DESC';

                        $result = $this->con->prepare($sql);
                        $result->bindValue(':student_username', $filter_username);

                        break;
                    case 'student':
                        $result = $this->con->prepare(
                            'SELECT
                               communication_id,
                               communication_body,
                               communication_date_added,
                               communication_time_added,
                               communication_file_id,
                               communication_comment_id
                             FROM
                              esuper_communication
                             WHERE
                               communication_type_id = 1
                             AND
                               communication_from_id = :user
                             ORDER BY
                               communication_date_added DESC, communication_time_added DESC'
                        );

                        $result->bindValue(':user', $user);

                        break;
                }

                break;
            case 'message':
                switch ($user_type) {
                    case 'staff';
                        $sql = 'SELECT
                                  c.communication_id,
                                  c.communication_body,
                                  c.communication_date_added,
                                  c.communication_time_added,
                                  c.communication_file_id,
                                  s.student_first,
                                  s.student_last,
                                  c.communication_from_id,
                                  c.communication_to_id
                                FROM
                                  esuper_communication c,
                                  esuper_student s
                                WHERE
                                  c.communication_type_id = 2
                                AND
                                  (s.student_username = c.communication_from_id OR s.student_username = c.communication_to_id)';

                        // Add filter if necessary
                        if ($filter_username != null) {
                            $sql .= ' AND
                                        (
                                          (c.communication_from_id = :staff_username AND c.communication_to_id = :student_username)
                                        OR
                                          (c.communication_from_id = :student_username AND c.communication_to_id = :staff_username)
                                        )';
                        }

                        $sql .= ' ORDER BY c.communication_date_added DESC, c.communication_time_added DESC';

                        $result = $this->con->prepare($sql);
                        $result->bindValue(':staff_username', $user);
                        $result->bindValue(':student_username', $filter_username);

                        break;
                    case 'student':
                        $sql = 'SELECT
                                  c.communication_id,
                                  c.communication_body,
                                  c.communication_date_added,
                                  c.communication_time_added,
                                  c.communication_file_id,
                                  s.staff_first,
                                  s.staff_last,
                                  c.communication_from_id,
                                  c.communication_to_id
                                FROM
                                  esuper_communication c,
                                  esuper_staff s
                                WHERE
                                  c.communication_type_id = 2
                                AND
                                  (s.staff_username = c.communication_from_id OR s.staff_username = c.communication_to_id)';

                        // Add filter if necessary
                        if ($filter_username != null) {
                            $sql .= ' AND
                                        (
                                          (c.communication_from_id = :staff_username AND c.communication_to_id = :student_username)
                                        OR
                                          (c.communication_from_id = :student_username AND c.communication_to_id = :staff_username)
                                        )';
                        }

                        $sql .= ' ORDER BY c.communication_date_added DESC, c.communication_time_added DESC';

                        $result = $this->con->prepare($sql);
                        $result->bindValue(':staff_username', $filter_username);
                        $result->bindValue(':student_username', $user);
                        break;
                }

                break;
        }

        try {
            $result->execute();
        } catch (PDOException $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $row = $result->fetchAll();
        $this->result = null;
        $this->response($row);
    }

    public function received($user, $user_type) {
        $this->type_id = 2;
        switch ($user_type) {

            case 'staff';
                $this->result = $this->con->prepare(
                    'SELECT
					   c.communication_id,
					   c.communication_body,
					   c.communication_date_added,
					   c.communication_time_added,
					   c.communication_file_id,
					   s.student_first,
					   s.student_last
					 FROM
					   esuper_communication c,
					   esuper_student s
                     WHERE
					   c.communication_type_id = ' . $this->type_id . '
					 AND
					   c.communication_to_id = "' . $user . '"
					 AND
					   s.student_username = c.communication_from_id'
                );
                break;

            case 'student':
                $this->result = $this->con->prepare(
                    'SELECT
					   c.communication_id,
					   c.communication_body,
					   c.communication_date_added,
					   c.communication_time_added,
					   c.communication_file_id,
					   s.staff_first,
					   s.staff_last
					 FROM
					   esuper_communication c,
					   esuper_staff s
					 WHERE
					   c.communication_type_id = ' . $this->type_id . '
					 AND
					   c.communication_to_id = "' . $user . '"
					 AND
					   s.staff_username = c.communication_from_id'
                );
                break;
        }

        try {
            $this->result->execute();
        } catch (PDOException $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $row = $this->result->fetchAll();
        $this->result = null;
        $this->response($row);
    }

    public function addComment($comment_id, $communication_id) {
        $result = $this->con->prepare(
            'UPDATE
               esuper_communication
             SET
               communication_comment_id = :comment_id
             WHERE
               communication_id = :communication_id'
        );
        $result->bindValue(':communication_id', strip_tags($communication_id));
        $result->bindValue(':comment_id', strip_tags($comment_id));

        try {
            $result->execute();
        } catch (PDOException $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $result = null;
        return true;
    }

    // Remove a communication from the database
    public function remove() {
    }

    // Set response variable
    public function response($var) {
        $this->response = $var;
    }

    // Return response variable when called
    public function getResponse() {
        return $this->response;
    }

}

?>