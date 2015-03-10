<?php
// Page which serves as the home page and login page

error_reporting(0);
require 'database-connection.php';
require 'validation.php';

// Initialise session
session_start();

$s = $_SESSION['currentUser'];

if($s['user_type'] != null){
RedirectToDashboard($s['user_type']);
}

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
            array_push($errorList, 'Enter a username');
        }

        // Validate password
        $password = mysqli_real_escape_string($link, stripslashes($_POST['password']));
        if (preg_match('/^$|\s+/', $password)) {
            array_push($errorList, 'Enter a password');
        }

        // Check for user in database
        if (($currentUser = LoginUser($link, $username, $password)) != null) {
            // Check if user is active
            if ($currentUser['is_active'] === '1') {
                // User is active
                $_SESSION['currentUser'] = $currentUser;

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
                RedirectToDashboard($_SESSION['currentUser']['user_type']);
            } else {
                // User is not active
                array_push($errorList, 'Your account is not active, contact student services');
            }

            // Close connection
            CloseConnection($link);
        } else {
            // Authentication failed
            array_push($errorList, 'Username and password match not found');
        }
    }
} else if (isset($_SESSION['username'])) {
    // User is already logged in, redirect to dashboard
    RedirectToDashboard($_SESSION['currentUser']['user_type']);
}

// Check for and display any errors
if (count($errorList) > 0) {
    $errorListOutput = DisplayErrorMessages($errorList);
}

// Function that redirects the user to the relevant dashboard
function RedirectToDashboard($userType) {
    header("Location: $userType/index.php");
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>eSupervision - Login</title>

    <meta name="author" content="Code Zero"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script type="text/javascript">
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
    </script>
</head>

<body>
<div class="container">
    <div class="center-align" id="header">
        <h5>eSupervision</h5>
        <img id="logo" class="responsive-img" src="imgs/greenwichLogo.png" alt="University of Greenwich logo"/>
    </div>
    <div class="row">
        <div class="card col s10 m6 l6 offset-s1 offset-m3 offset-l3">
            <form action="index.php" method="post" id="login">
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

                <div class="card-content">
                    <span class="card-title green-text">Login</span>

                    <div class="input-field">
                        <label for="username">User ID:</label>
                        <input id="username" value="<?php echo $username; ?>" name="username" type="text" size="30"
                               maxlength="30" onkeyup="ValidateUsername(this.value);"
                               onblur="ValidateUsername(this.value);"/>
                        <span id="usernameValidation" class="red-text text-light-3 validation-error"></span>
                    </div>
                    <div class="input-field">
                        <label for="password">Password:</label>
                        <input id="password" name="password" type="password" size="30" maxlength="30"
                               onkeyup="ValidatePassword(this.value);" onblur="ValidatePassword(this.value);"/>
                        <span id="passwordValidation" class="red-text text-light-3 validation-error"></span>
                    </div>
                    <div>
                        <input value="rememberUsername" name="rememberUsername[]" id="rememberUsername"
                               type="checkbox" checked="checked"/>
                        <label for="rememberUsername" class="green-text">Remember user</label>
                    </div>
                    <div
                        class="red-text text-light-3 validation-error"><?php echo $outputText; ?><?php echo $errorListOutput; ?></div>
                </div>
                <div class="card-action">
                    <button class="c_right_align btn-flat green waves-effect waves-light white-text" type="submit"
                            value="Login" id="submitLogin" name="login" onclick="return ValidateForm();">Login
                    </button>
                    <a class="c_left-align card-link" href="http://ach-support.gre.ac.uk/general/password.asp"
                       target="_blank">Password
                        reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <p class="col s10 m6 l6 offset-s1 offset-m3 offset-l3 notice">This website uses cookies to improve your
            experience. By continuing you agree to these cookies being stored on your computer.</p>
    </div>
</div>
</body>

</html>