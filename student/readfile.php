<?php // http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/uploading-files-to-mysql-database.aspx

// if id is set then get the file with the id from database

include '../classes/security.class.php';
include '../classes/file.class.php';

$f = new File ();
$f->readFile ( $_POST['file_id'] );

$file = $f->getResponse ();

// echo "<pre>";
// print_r ($f->getResponse ());	
// echo "</pre>";

// header("Content-length: ".$file['file_size']);
// header("Content-type: text/plain");
// header("Content-Disposition: attachment; filename= ".$file['file_name']);
// header("Content-length: $size");
// 
// exit;
// header("Content-length: ".$file['file_size']);
print $file['file_content'];


// ob_start();

// $file='apr07pgdwereport.pdf';
// header("Content-Disposition: attachment; filename=$file");
// header("Content-Type: application/force-download");
// header("Content-Type: application/octet-stream");
// header("Content-Type: application/download");
// header("Content-Description: File Transfer");
// header("Content-Length: " . filesize($file));
// flush(); // this doesn't really matter.

// $fp = fopen($file['file_content'], "r");
// while (!feof($fp))
// {
// echo fread($fp, 200000);
// flush(); // this is essential for large downloads
// }
// fclose($fp);

// ob_end_flush();

?>