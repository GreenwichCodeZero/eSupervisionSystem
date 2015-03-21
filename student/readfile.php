<?php // http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/uploading-files-to-mysql-database.aspx

// if id is set then get the file with the id from database
ob_start();
include '../classes/security.class.php';
include '../classes/file.class.php';

$f = new File ();

if (empty ($_POST['file_id'])) {
    header ('location : ../');
}

$f->readFile ( $_POST['file_id'] );

$file = $f->getResponse ();

// echo "<pre> _POST";
// print_r ($_POST);	
// echo "</pre>";
// echo "<br><br>";
// echo "<pre> FILE";
// print_r ($file);	
// echo "</pre>";

echo "<pre>";
print_r ($file[0]['file_content']);
echo "<pre>";

header("Content-length: ".$file[0]['file_size']);
header("Content-type: ".$file[0]['file_type']);
header("Content-Disposition: attachment; filename=eSupervision-".$file[0]['file_name']);
echo( $file['file_content']);

ob_end_flush();

?>