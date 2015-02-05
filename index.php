<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb">

<head>
    <title>Login</title>
    <meta name="author" content="Code Zero"/>
    <link href="styles.css" rel="stylesheet" type="text/css"/>
</head>

<body>

<div class="container">

    <h2>Login</h2>

    <form action="index.php" method="post" id="login">

        <fieldset>

            <legend>
                Login to your account
            </legend>

            <table summary="Table containing information fields required to login to the website">
                <tbody>
                <tr>
                    <td>
                        <p>
                            <label for="username">Username:</label>
                        </p>
                    </td>
                    <td>
                        <p>
                            <input id="username" value="" name="username" type="text" size="30" maxlength="30"/>
                            <small><span id="usernameValidation" class="validation-error"></span></small>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>
                            <label for="password">Password:</label>
                        </p>
                    </td>
                    <td>
                        <p>
                            <input id="password" name="password" type="password" size="30" maxlength="30"/>
                            <small><span id="passwordValidation" class="validation-error"></span></small>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p>
                            <input type="submit" value="Login" name="login"/>

                            <input id="rememberUsername" value="rememberUsername" name="rememberUsername[]"
                                   type="checkbox" checked="checked"/>
                            <small><label for="rememberUsername">Remember username</label></small>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="notice">
                <small>This website uses cookies to improve your experience. By continuing you agree to these cookies
                    being stored on your computer.
                </small>
            </p>

        </fieldset>

    </form>

</div>

</body>

</html>