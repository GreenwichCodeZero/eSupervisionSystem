<?php
// Page which serves as the home page and login page

error_reporting(0);
require 'database-connection.php';
require 'validation.php';

// Initialise session
session_start();

$errorList = array();
$outputText = $errorListOutput = '';

if (isset($_POST['login'])) {
    // 'Login' button pressed
    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        $outputText .= '<p class="error">Error connecting to database, please try again.</p>';
    } else {
        // Server-side validation
        // Validate username
        $username = mysqli_real_escape_string($link, stripslashes($_POST['username']));
        if (preg_match('/^$|\s+/', $username)) {
            array_push($errorList, 'Username not entered');
        }

        // Validate password
        $password = mysqli_real_escape_string($link, stripslashes($_POST['password']));
        if (preg_match('/^$|\s+/', $password)) {
            array_push($errorList, 'Password not entered');
        }

        // Check for user in database
        if (($_SESSION['currentUser'] = LoginUser($link, $username, $password)) != null) {
            // Logged in successfully
            // Check if 'remember username' was checked
            if (isset($_POST['rememberUsername'])) {
                // Checked, set cookie to expire in 28 days
                setcookie('username', $username, (time() + (3600 * 24 * 28)));
            } else {
                // Unchecked, set cookie to expire one hour previously
                setcookie('username', '', (time() - 3600));
            }

            // Redirect to dashboard
            header('Location: dashboard.php' . (SID != '' ? '?' . SID : ''));

            // Close connection
            CloseConnection($link);
        } else {
            // Authentication failed
            array_push($errorList, 'Username and password match not found');
        }
    }
} elseif (isset($_SESSION['username'])) {
    // User is already logged in, redirect to dashboard
    header('Location: dashboard.php' . (SID != '' ? '?' . SID : ''));
}

// Check for and display any errors
if (count($errorList) > 0) {
    $errorListOutput = DisplayErrorMessages($errorList);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">

    <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script type="text/javascript">
        <!--
        // Client-side form validation
        // Function to display any error messages on form submit
        /**
         * @return {boolean}
         */
        function ValidateForm() {
            var isValid = true;

            // Validate username
            if (ValidateUsername(document.getElementById('username').value) != '') isValid = false;

            // Validate password
            if (ValidatePassword(document.getElementById('password').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the username
        function ValidateUsername(username) {
            var output;
            if (/^$|\s+/.test(username)) {
                output = 'Username is required';
            } else {
                output = '';
            }

            document.getElementById('usernameValidation').innerHTML = output;
            return output;
        }

        // Function to validate the password
        function ValidatePassword(password) {
            var output;
            if (/^$|\s+/.test(password)) {
                output = 'Password is required';
            } else {
                output = '';
            }

            document.getElementById('passwordValidation').innerHTML = output;
            return output;
        }
        //-->
    </script>
</head>

<body>

<div class="container">


    <h1>eSupervisor Dashboard</h1>



    <img class="logo" src="imgs/greenwichLogo.png" alt="University of Greenwich logo" />



<div class="row formLogin">
<div class="col s10 m6 l6 offset-m3 offset-l3 offset-s1">

<div class="card">

    <form action="index.php<?php (SID != '' ? '&amp;' . SID : ''); ?>" method="post" id="login" class="col s10 m6 l6">

        <?php
        // Sticky form fields
        if (isset($_COOKIE['username'])) {
            // Add username to username text box if cookie is saved
            $username = $_COOKIE['username'];
        } else {
            $username = '';
        }

        extract($_POST);
        ?>

        <fieldset>

            <legend>
                Login
            </legend>

<div class="row">
      <div class="input-field col s12 m12 l12">
                            <label for="username" id="labelUsername">User ID:</label>
      
                            <input id="username" value="<?php echo $username; ?>" name="username" type="text" size="30"
                                   maxlength="30" onkeyup="ValidateUsername(this.value);"
                                   onblur="ValidateUsername(this.value);"/>
                            <small><span id="usernameValidation" class="validation-error"></span></small>
                            </div>
    
<div class="row"></div>
          <div class="input-field col s12 m12 l12">

                            <label for="password">Password:</label>

                            <input id="password" name="password" type="password" size="30" maxlength="30"
                                   onkeyup="ValidatePassword(this.value);" onblur="ValidatePassword(this.value);"/>
                            <small><span id="passwordValidation" class="validation-error"></span></small>

</div>
</div>



<input id="rememberUsername" value="rememberUsername" name="rememberUsername[]" type="checkbox" checked="checked"/>
<small><label for="rememberUsername">Remember username</label></small>

<div class="card-action">

<div class="row">
<div class="col s6 m6 l8">
<a href="http://ach-support.gre.ac.uk/general/password.asp" target="_blank">Password reset</a></div>
<div class="col s6 m6 l4">
              


        <input type="submit" value="Login" id="submitLogin" name="login" onclick="return ValidateForm();"/>

</div>

</div>

            <?php echo $outputText; ?>
            <?php echo $errorListOutput; ?>

        </fieldset>
</div>
    </form>
    </div>
    </div>

<div class="row">
<div class="col s10 m6 l6 offset-m3 offset-l3 offset-s1">
            <p class="notice">
                <small>This website uses cookies to improve your experience. By continuing you agree to these cookies
                    being stored on your computer.
                </small>
            </p>
            </div>
            </div>
</div>

</body>

</html>