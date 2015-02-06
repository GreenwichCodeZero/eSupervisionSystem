<?php

header('Content-type: text/plain');

if (isset($_GET['pw'])) {
    exit(password_hash($_GET['pw'], PASSWORD_DEFAULT));
}

?>