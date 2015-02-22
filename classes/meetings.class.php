<?php

require_once 'security.class.php'; // include security class
require_once 'file.class.php'; // include file class

class Meeting {

// This is the description for the class: meeting (eSupervision).
// * 
// * @class 	 	Meeting
// * @description 	Use to handle all database requests for meetings
// * @package    	eSupervision
// * @authors       Dwayne Brown & Mark Tickner 
// * @version    	1.1

    private $action;
    private $response;
    private $con;

    private $meeting_id;
    private $from;
    private $to;
    private $date_addded = '';
    private $time_addded = '';
    private $body;
    private $meeting_type;

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

    public function insert() {

        try {
            $this->validate();
        } catch (Exception $e) {
            throw new Exception ($e->getMessage());
            exit;
        }

        $this->action = (isset ($_POST['meeting_action'])) ? $_POST['meeting_action'] : null;

        switch ($this->action) {
            case 'sendmessage':
                echo "message:";
                $this->add('message');
                break;

            case 'posttoblog':
                echo "Blog Post: id1";
                $this->add('blog');
                break;

            default:
                echo "nothing to do <pre>";
                print_r($_POST);
                exit;
                break;
        }
    }

    public function validate() {
        foreach ($_POST as $key => $value) {
            if (empty ($value)) {
                throw new Exception ('Please complete all fields');
                exit;
            }
        }
    }

    // Add a new meeting to the database
    public function add($type) {

        switch ($type) {
            case 'message':
                $this->type = 2;
                $this->to = $_POST ['meeting_to_id'];
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


        $this->from = $_POST ['meeting_from_id'];
        $this->date_addded = time();
        $this->time_addded = time();
        $this->body = $_POST ['meeting_body'];

        $result = $this->con->prepare(
            "INSERT INTO `esuper_meeting` (meeting_from_id,meeting_to_id, meeting_date_added, meeting_time_added, meeting_type_id, meeting_body)
			VALUES 
			(:from_id,:to_id,:date_added,:time_added,:type_id ,:comm_body);");

        $result->bindValue(':from_id', $this->from);
        $result->bindValue(':to_id', $this->to);
        $result->bindValue(':date_added', $this->date_addded);
        $result->bindValue(':time_added', $this->date_addded);
        $result->bindValue(':type_id', $this->type);
        $result->bindValue(':comm_body', $this->body);
        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        // $row = $result->fetch(PDO::FETCH_ASSOC);
        $result = null;
        $this->response('Your content was successfully committed');

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
    public function getAll($type, $username) {

        switch ($type) {
            case 'blog':
                $this->type_id = 1;
                break;

            case 'message':
                $this->type_id = 2;
                break;

        }
        $result = $this->con->prepare(
            'SELECT m.meeting_id, m.meeting_date, mt.timeslot_time AS meeting_time, m.meeting_title, m.meeting_content, s.meeting_status_name AS meeting_status, t.meeting_type_name AS meeting_type, st.student_first, st.student_last
             FROM esuper_meeting m
             JOIN esuper_meeting_timeslot mt ON mt.timeslot_id = m.meeting_timeslot_id
             JOIN esuper_meeting_status s ON s.meeting_status_id = m.meeting_status_id
             JOIN esuper_meeting_type t ON t.meeting_type_id = m.meeting_type_id
             JOIN esuper_student st ON st.student_username = m.meeting_student_id
             WHERE meeting_student_id = :username
             OR meeting_staff_id = :username
             ORDER BY m.meeting_id DESC'
        );
        $result->bindValue(':username', $username);

        try {
            $result->execute();
        } catch (PDOException $e) {
            echo "ERROR:";
            echo "\n\n\r\r" . $e->getMessage();
            exit;
        }

        $row = $result->fetchAll();
        $result = null;
        $this->response($row);
    }

    public function response($var) {
        $this->response = $var;
    }

    public function getResponse() {
        return $this->response;
    }
}

?>