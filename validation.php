<?php
// Form validation functions

// Redirect direct access to this file
// Source: http://board.phpbuilder.com/showthread.php?10263469#post10986948
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // File requested directly, redirect to home page
    header('Location: index.php');
} else {
    // Function to display any error messages
    function DisplayErrorMessages($errorList) {
        $errorListOutput = '<ul>';

        for ($i = 0; $i < count($errorList); $i++) {
            $errorListOutput .= '<li>' . $errorList[$i] . '</li>';
        }

        $errorListOutput .= '</ul>';

        return $errorListOutput;
    }
}
?>