<?php
error_reporting(0);

include 'authspire.php';
include 'details.php';

session_start();

$authSpireAPI = new authSpire\api($app_name, $userid, $secret, $currentVersion, $publicKey);

if (!isset($_SESSION['user_username'])) // if user not logged in
{
    header("Location: ../");
    exit();
}

$user = $_SESSION["user_username"];
$user_email = $_SESSION["user_email"];
$user_ip = $_SESSION["user_ip"];
$user_expires = $_SESSION["user_expires"];
$user_hwid = $_SESSION["user_hwid"];
$user_last_login = $_SESSION["user_last_login"];
$user_created_at = $_SESSION["user_created_at"];
$user_variable = $_SESSION["user_variable"];
$user_level = $_SESSION["user_level"];

$application_status = $_SESSION["application_status"];
$application_hash = $_SESSION["application_hash"];
$application_name = $_SESSION["application_name"];
$application_version = $_SESSION["application_version"];
$update_url = $_SESSION["update_url"];
$user_count = $_SESSION["user_count"];





?>
<html>
<head>
  <title>AuthSpire API example</title>
  <link rel="stylesheet" href="https://authspire.com/style/style.css" type="text/css">
  <link rel="icon" type="image/x-icon" href="../images/logofav.ico">
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="sweetalert2.all.min.js"></script>
</head>
<body id="panel-body">
    

    <section id="panel-interface">


        <h3 id="i-name">
			User Details
		</h3>
            <div id="account-table">
                <table width="100%">
                    <tr>
                        <th>Welcome back</th>
                        <td><?php echo $user; ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo $user_email; ?></td>
                    </tr>
                    <tr>
                        <th>IP</th>
                        <td><?php echo $user_ip; ?></td>
                    </tr>
                    <tr>
                        <th>Expires</th>
                        <td><?php echo $user_expires; ?></td>
                    </tr>
                    <tr>
                        <th>HWID</th>
                        <td><?php echo $user_hwid; ?></td>
                    </tr>
                    <tr>
                        <th>Last-Login</th>
                        <td><?php echo $user_last_login; ?></td>
                    </tr>
                    <tr>
                        <th>Created-At</th>
                        <td><?php echo $user_created_at; ?></td>
                    </tr>
                    <tr>
                        <th>User Variable</th>
                        <td><?php echo $user_variable; ?></td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td><?php echo $user_level; ?></td>
                    </tr>
                </table>
            </div>

            <div id="manage-licenses">
                <h1 id="title-of-create-app">
                    Add Log
                </h1>
                <form method="post">
                    <div id="inputBox-Licenses">
                        <span>Action</span>
                        <input type="text" name="action" required="required" maxlength="256">
                    </div>
                    <button name="log">Add Log</button>
                </form>
            </div>

            <div id="manage-licenses">
                <h1 id="title-of-create-app">
                    Get Variable
                </h1>
                <form method="post">
                    <div id="inputBox-Licenses">
                        <span>Secret</span>
                        <input type="text" name="secret" required="required" maxlength="256">
                    </div>
                    <button name="variable">Get</button>
                </form>
            </div>

            <div id="manage-licenses">
                <form method="post">
                    <button name="sign_out">Sign out</button>
                </form>
            </div>
    </section>

    
</div>

<?php
    if (isset($_POST['sign_out'])) {
        session_destroy();
        header("Location: ../");
        exit();
    }

    if (isset($_POST['variable'])) {
        $variable = $authSpireAPI->get_variable($_POST['secret']);
        echo '<script type="text/JavaScript"> 
                      Swal.fire({
                          title: "Value: ' . trim($variable) . '",
                          icon: "success",
                      })
                  </script>';
    }

    if (isset($_POST['log'])) {
        if($authSpireAPI->add_log($_SESSION["user_username"], $_POST['action'])) {
            echo '<script type="text/JavaScript"> 
                      Swal.fire({
                          title: "Log added!",
                          icon: "success",
                          timer: 5000,
                      })
                  </script>';
        }
    }
?>

</body>
</html>