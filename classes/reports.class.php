<?php

class Reports {

    private $response;

    function __construct() {
        $s = new Security ();
        try {
            $this->con = $s->db();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }


public function notLoggedIn7Days() {
        $result = $this->con->prepare(
            'SELECT
               *
             FROM
               esuper_student
             WHERE
               last_login_date < 7
               ');

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


public function getResponse() {
        return $this->response;
    }

    private function response($var) {
        return $this->response = $var;
    }


}