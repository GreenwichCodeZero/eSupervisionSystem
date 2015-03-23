<?php

class Security {

    private $db_host = 'studb.cms.gre.ac.uk';
    private $db_name = 'mdb_codezero';
    private $db_user = 'codezero';
    private $db_pass = 'tickner14';
    private $db_base = 'mdb_codezero';

    public function clean($input) {
        $search = array(
            '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
        );

        $output = preg_replace($search, '', $input);
        return $output;

        if (is_array($input)) {
            foreach ($input as $var => $val) {
                $output[$var] = sanitize($val);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $input = stripslashes($input);
            }

            $input = cleanInput($input);
            $output = mysql_real_escape_string($input);
        }

        return $output;
    }

    private function dbconnect() {
        try {
            $this->connection = new PDO('mysql:host=' . $this->db_host . '; dbname=' . $this->db_name, $this->db_user, $this->db_pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $err) {
            throw new Exception ($err->getMessage());
            exit;
        }

        return $this->connection;
    }

    public function db() {
        return $this->dbconnect();
    }

}

?>