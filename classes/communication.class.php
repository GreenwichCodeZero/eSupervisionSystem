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
    private $time_addded = '';
    private $body;
    private $communication_type;

    // Load variables from POST into object
    public function __construct() {
        date_default_timezone_set('Europe/London');

        $s = new Security ();
        $s->clean($_POST);
        try {
            $this->con = $s->db();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function insert($from_user) {
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
                // echo "message:";
                $this->add('message');
                break;

            case 'posttoblog':
                // echo "Blog Post: id1";
                $this->add('blog');
                break;

            default:
                // echo "nothing to do <pre>";
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
    public function add($type) {
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
                echo "<br>NO POST VALUES<br>";
                print_r($_POST);
                exit;
                break;
        }

        $this->file_id = 0;

        if ($_FILES['fileToUpload']['size'] > 0) {

            $f = new File ();
            try {
                $f->add($this->from);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }

            $this->file_id = $f->getResponse();

            $this->response('file uploaded: ' . $this->file_id);
        }

        $this->from = $_POST ['communication_from_id'];
        $this->to = $_POST ['communication_to_id'];
        $this->date_addded = time();
        $this->time_addded = time();
        $this->body = $_POST ['communication_body'];

        $result = $this->con->prepare(
            "INSERT INTO `esuper_communication` (communication_from_id,communication_to_id, communication_date_added, communication_time_added, communication_type_id, communication_body, communication_file_id)
			VALUES (:from_id,:to_id,:date_added,:time_added,:type_id ,:comm_body, :comm_file_id);");

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
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        // $row = $result->fetch(PDO::FETCH_ASSOC);
        $result = null;

        $headers = 'From: eSupervision System <esupervision@greenwich.ac.uk>' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        if (mail('bd118@greenwich.ac.uk, ' . $this->from . '@greenwich.ac.uk',
            'New Message Received', 'A new message was submitted and is waiting for you on the eSupervision System.', $headers)) {
            $this->response('Your content was commited successfully and your tutor has been notified .');
        } else {
            $this->response('Your content was commited successfully but your tutor could not be notified.');
        }
    }

    // Find a comment by comment id, type, who posted etc.
    public function getAll($type, $user, $user_type = null, $filter_username = null) {
        switch ($type) {
            case 'blog':
                $type_id = 1;
                $result = $this->con->prepare(
                    'SELECT
						`esuper_communication`.`communication_id`
						, `esuper_communication`.`communication_body`
						, `esuper_communication`.`communication_date_added`
						, `esuper_communication`.`communication_time_added`
						, `esuper_communication`.`communication_file_id` 
						FROM
						`esuper_communication`
						 WHERE 
						`esuper_communication`.`communication_type_id` = :type_id ORDER BY communication_date_added DESC');
                $result->bindValue(':type_id', $type_id);

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
                        $result = $this->con->prepare(
                            'SELECT
						`esuper_communication`.`communication_id`
						, `esuper_communication`.`communication_body`
						, `esuper_communication`.`communication_date_added`
						, `esuper_communication`.`communication_time_added`
						, `esuper_communication`.`communication_file_id`
						, `esuper_staff`.`staff_first`
						, `esuper_staff`.`staff_last`
						FROM
						`esuper_communication`,
						`esuper_staff`
						 WHERE
						`esuper_communication`.`communication_type_id` = :type_id
						AND
						`esuper_communication`.`communication_from_id` = :user
						AND
						`esuper_staff`.`staff_username` = `esuper_communication`.`communication_to_id`
						ORDER BY `esuper_communication`.communication_date_added DESC, `esuper_communication`.communication_time_added DESC');
                        $result->bindValue(':type_id', $type_id);
                        $result->bindValue(':user', $user);

                        break;
                }

                break;
        }

        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
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
						`esuper_communication`.`communication_id`
						, `esuper_communication`.`communication_body`
						, `esuper_communication`.`communication_date_added`
						, `esuper_communication`.`communication_time_added`
						, `esuper_communication`.`communication_file_id` 
						, `esuper_student`.`student_first`
						, `esuper_student`.`student_last`
						FROM
						`esuper_communication`,
						`esuper_student`
						 WHERE 
						`esuper_communication`.`communication_type_id` =' . $this->type_id . '
						AND 
						`esuper_communication`.`communication_to_id` = "' . $user . '"
						AND 
						`esuper_student`.`student_username` = `esuper_communication`.`communication_from_id`'
                );
                break;

            case 'student':
                $this->result = $this->con->prepare(
                    'SELECT
						`esuper_communication`.`communication_id`
						, `esuper_communication`.`communication_body`
						, `esuper_communication`.`communication_date_added`
						, `esuper_communication`.`communication_time_added`
						, `esuper_communication`.`communication_file_id` 
						, `esuper_staff`.`staff_first`
						, `esuper_staff`.`staff_last`
						FROM
						`esuper_communication`,
						`esuper_staff`
						 WHERE 
						`esuper_communication`.`communication_type_id` =' . $this->type_id . '
						AND 
						`esuper_communication`.`communication_to_id` = "' . $user . '"
						AND 
						`esuper_staff`.`staff_username` = `esuper_communication`.`communication_from_id`'
                );
                break;
        }

        try {
            $this->result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        $row = $this->result->fetchAll();
        $this->result = null;
        $this->response($row);
    }

    // Remove a communication from the database
    public function remove() {
    }

    public function response($var) {
        $this->response = $var;
    }

    public function getResponse() {
        return $this->response;
    }
}

?>