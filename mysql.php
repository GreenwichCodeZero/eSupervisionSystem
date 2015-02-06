<?php
// Database credentials, protected from viewing in the browser

// Prevent direct access to this file
// Source: http://board.phpbuilder.com/showthread.php?10263469#post10986948
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // File requested directly, redirect user
    header('Location: http://stuiis.cms.gre.ac.uk/codezero/');
} else {
    $dbHost = 'mysql.cms.gre.ac.uk';
    $dbUsername = 'codezero';
    $dbPassword = 'tickner14';
    $dbName = 'mdb_codezero';
}
?>