<?php
error_reporting(0);

include 'authspire.php';
include 'details.php';

if (isset($_SESSION['user_username']))
{
    header("Location: /main.php");
    exit();
}

$authSpireAPI = new authSpire\api($app_name, $userid, $secret, $currentVersion, $publicKey);


if(!$_SESSION['initialized'])
{
  $authSpireAPI->init();
}

?>
<html>
<head>
  <title>AuthSpire API example</title>
  <link rel="stylesheet" href="https://authspire.com/style/style.css" type="text/css">
  <link rel="icon" type="image/x-icon" href="../images/logofav.ico">
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="sweetalert2.all.min.js"></script>
</head>
<body id="bodyLogin">
<div id="box-register" style="height:700px;">
        <form id="form-login" method="post">
            <h2>AuthSpire PHP</h2>
            <div id="inputBox">
                <input maxlength="256" type="text" id="username" name="username" onkeyup='saveValue(this);'>
                <span>Username</span>
                <i></i>
            </div>
            <div id="inputBox">
                <input maxlength="256" type="password" name="password">
                <span>Password</span>
                <i></i>
            </div>
            <div id="inputBox">
                <input maxlength="256" type="text" name="license">
                <span>License</span>
                <i></i>
            </div>
            <div id="inputBox">
                <input maxlength="256" type="text" name="email">
                <span>Email</span>
                <i></i>
            </div>
            <br>
            <button style="height:50px; background:#5c5c8a; border:#5c5c8a; color:#FFF;" name="register" maxlength="256">
            <span>Register</span>
            </button>
            <br>
            <button style="height:50px; background:#5c5c8a; border:#5c5c8a; color:#FFF;" name="login" maxlength="256">
            <span>Login</span>
            </button>
            <br>
            <button style="height:50px; background:#5c5c8a; border:#5c5c8a; color:#FFF;" name="license_only" maxlength="256">
            <span>License</span>
            </button>
        </div>

    </div>
</div>

<?php

  if (isset($_POST['login'])) {
    // login with username and password
    if($authSpireAPI->login($_POST['username'], $_POST['password'])) {
      echo "<meta http-equiv='Refresh' Content='2; url=main.php'>";

      echo '<script type="text/JavaScript"> 
                Swal.fire({
                    title: "Login successful!",
                    icon: "success",
                    timer: 5000,
                })
            </script>';
    }
  }

  if (isset($_POST['register'])) {
	// register using username, password, license and email
    if($authSpireAPI->register($_POST['username'], $_POST['password'], $_POST['license'], $_POST['email'])) {
      echo "<meta http-equiv='Refresh' Content='2; url=main.php'>";

      echo '<script type="text/JavaScript"> 
                Swal.fire({
                    title: "Account registered!",
                    icon: "success",
                    timer: 5000,
                })
            </script>';
    }
  }

  if (isset($_POST['license_only'])) {
    // register using username, password, license and email
      if($authSpireAPI->license($_POST['license'])) {
        echo "<meta http-equiv='Refresh' Content='2; url=main.php'>";

        echo '<script type="text/JavaScript"> 
                Swal.fire({
                    title: "Login successful!",
                    icon: "success",
                    timer: 5000,
                })
            </script>';
      }
    }
?>

</body>
</html>