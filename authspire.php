<?php
namespace authSpire;

session_start();

require_once('phpseclib/Crypt/Base.php');
require_once('phpseclib/Crypt/Rijndael.php');
require_once('phpseclib/Crypt/AES.php');
require_once('phpseclib/Math/BigInteger.php');
require_once('phpseclib/Crypt/RSA.php');
require_once('phpseclib/Crypt/Hash.php');
require_once('phpseclib/Crypt/Random.php');

const ServerOffline = "Server is currently not responding, try again later!";
const RegisterInvalidLicense = "The license you entered is invalid or already taken!";
const RegisterInvalidDetails = "You entered an invalid username or email!";
const RegisterUsernameTaken = "This username is already taken!";
const RegisterEmailTaken = "This email is already taken!";
const UserExists = "A user with this username already exists!";
const UserLicenseTaken = "This license is already binded to another machine!";
const UserLicenseExpired = "Your license has expired!";
const UserBanned = "You have been banned for violating the TOS!";
const UserBlacklisted = "Your IP/HWID has been blacklisted!";
const VPNBlocked = "You cannot use a vpn with our service! Please disable it.";
const InvalidUser = "User doesn't exist!";
const InvalidUserCredentials = "Username or password doesn't match!";
const InvalidLoginInfo = "Invalid login information!";
const InvalidLogInfo = "Invalid log information!";
const LogLimitReached = "You can only add a maximum of 50 logs as a free user, upgrade to premium to enjoy no log limits!";
const UserLimitReached = "You can only add a maximum of 30 users as a free user, upgrade to premium to enjoy no user limits!";
const FailedToAddLog = "Failed to add log, contact the provider!";
const InvalidApplication = "Application could not be initialized, please check your secret and userid.";
const ApplicationPaused = "This application is currently under construction, please try again later!";
const NotInitialized = "Please initialize your application first!";
const NotLoggedIn = "Please log into your application first!";
const ApplicationDisabled = "Application has been disabled by the provider.";
const ApplicationManipulated = "File corrupted! This program has been manipulated or cracked. This file won't work anymore.";

const endpoint = "https://api.authspire.com/v1";


class api
{
    public $app_name, $userid, $secret, $currentVersion, $publicKey;

    function __construct($app_name, $userid, $secret, $currentVersion, $publicKey)
    {
        $this->app_name = $app_name;
        $this->userid = $userid;
        $this->secret = $secret;
        $this->currentVersion = $currentVersion;
        $this->publicKey = $this->FormatPublicKey($publicKey);
    }

    function init()
    {
        if ($this->app_name == "" || $this->userid == "" || $this->secret == "" || $this->currentVersion == "" || $this->publicKey == "") {
            die(NotInitialized);
        }

        $key = $this->randomString(32);
        $iv = $this->randomString(16);
     
        $data = array (
            "action" => base64_encode("app_info"),
            "userid" => base64_encode($this->userid),
            "app_name" => base64_encode($this->app_name),
            "secret" => $this->aes_encrypt($this->secret, $key, $iv),
            "version" => $this->aes_encrypt($this->currentVersion, $key, $iv),
            "hash" => "",
            "key" => $this->RSAEncrypt($key, $this->publicKey),
            "iv" => $this->RSAEncrypt($iv, $this->publicKey),
        );

        $response = json_decode($this->post($data));

        if($response->status == "success") {
            $_SESSION['application_status'] = $this->aes_decrypt($response->application_status, $key, $iv);
            $_SESSION['application_hash'] = $this->aes_decrypt($response->application_hash, $key, $iv);
            $_SESSION['application_name'] = $this->aes_decrypt($response->application_name, $key, $iv);
            $_SESSION['application_version'] = $this->aes_decrypt($response->application_version, $key, $iv);
            $_SESSION['update_url'] = $this->aes_decrypt($response->update_url, $key, $iv);
            $_SESSION['user_count'] = $this->aes_decrypt($response->user_count, $key, $iv);
            $_SESSION['initialized'] = true;
        } else if ($response->status == "update_available") {
            $_SESSION['update_url'] = $this->aes_decrypt($response->update_url, $key, $iv);
            $_SESSION['application_version'] = $this->aes_decrypt($response->application_version, $key, $iv);
            return FALSE;
        } else if ($response->status == "invalid_hash") {
            die(ApplicationManipulated);
        } else if ($response->status == "invalid_app") {
            die(InvalidApplication);
        } else if ($response->status == "paused") {
            die(ApplicationPaused);
        } else if ($response->status == "locked") {
            die(ApplicationDisabled);
        }
        return TRUE;
    }


    function login($username, $password)
    {
        if (!$_SESSION['initialized']) {
            die(NotInitialized);
        }

        $key = $this->randomString(32);
        $iv = $this->randomString(16);

        $data = array (
            "action" => base64_encode("login"),
            "userid" => base64_encode($this->userid),
            "app_name" => base64_encode($this->app_name),
            "secret" => $this->aes_encrypt($this->secret, $key, $iv),
            "username" => $this->aes_encrypt($username, $key, $iv),
            "password" => $this->aes_encrypt($password, $key, $iv),
            "hwid" => $this->aes_encrypt("123", $key, $iv), // your way of getting the users unique identifier
            "key" => $this->RSAEncrypt($key, $this->publicKey),
            "iv" => $this->RSAEncrypt($iv, $this->publicKey),
        );

        $response = json_decode($this->post($data));
        if($response->status == "ok") {
            $_SESSION['user_username'] = $this->aes_decrypt($response->username, $key, $iv);
            $_SESSION['user_email'] = $this->aes_decrypt($response->email, $key, $iv);
            $_SESSION['user_ip'] = $this->aes_decrypt($response->ip, $key, $iv);
            $_SESSION['user_expires'] = $this->aes_decrypt($response->expires, $key, $iv);
            $_SESSION['user_hwid'] = $this->aes_decrypt($response->hwid, $key, $iv);
            $_SESSION['user_last_login'] = $this->aes_decrypt($response->last_login, $key, $iv);
            $_SESSION['user_created_at'] = $this->aes_decrypt($response->created_at, $key, $iv);
            $_SESSION['user_variable'] = $this->aes_decrypt($response->variable, $key, $iv);
            $_SESSION['user_level'] = $this->aes_decrypt($response->level, $key, $iv);

            $app_variables = explode(";", $this->aes_decrypt($response->app_variables, $key, $iv));
            foreach ($app_variables as $app_variable) {
                $app_variable_split = explode(":", $app_variable);
                if (count($app_variable_split) == 2) {
                    $_SESSION['variables'][$app_variable_split[0]] = $app_variable_split[1];
                }
            }
            return TRUE;
        } else if ($response->status == "invalid_user") {
            $this->error(InvalidUserCredentials);
        } else if ($response->status == "invalid_details") {
            $this->error(InvalidUserCredentials);
        } else if ($response->status == "license_expired") {
            $this->error(UserLicenseExpired);
        } else if ($response->status == "invalid_hwid") {
            $this->error(UserLicenseTaken);
        } else if ($response->status == "banned") {
            $this->error(UserBanned);
        } else if ($response->status == "blacklisted") {
            $this->error(UserBlacklisted);
        } else if ($response->status == "vpn_blocked") {
            $this->error(VPNBlocked);
        } else {
            return FALSE;
        }
    }



    function register($username, $password, $license, $email)
    {
        if (!$_SESSION['initialized']) {
            die(NotInitialized);
        }

        $key = $this->randomString(32);
        $iv = $this->randomString(16);

        $data = array (
            "action" => base64_encode("register"),
            "userid" => base64_encode($this->userid),
            "app_name" => base64_encode($this->app_name),
            "secret" => $this->aes_encrypt($this->secret, $key, $iv),
            "username" => $this->aes_encrypt($username, $key, $iv),
            "password" => $this->aes_encrypt($password, $key, $iv),
            "license" => $this->aes_encrypt($license, $key, $iv),
            "email" => $this->aes_encrypt($email, $key, $iv),
            "hwid" => $this->aes_encrypt("123", $key, $iv), // your way of getting the users unique identifier
            "key" => $this->RSAEncrypt($key, $this->publicKey),
            "iv" => $this->RSAEncrypt($iv, $this->publicKey),
        );

        $response = json_decode($this->post($data));
        if($response->status == "user_added") {
            return TRUE;
        } else if ($response->status == "user_limit_reached") {
           $this->error(UserLimitReached);
        } else if ($response->status == "invalid_details") {
            $this->error(RegisterInvalidDetails);
        } else if ($response->status == "email_taken") {
            $this->error(RegisterEmailTaken);
        } else if ($response->status == "invalid_license") {
            $this->error(RegisterInvalidLicense);
        } else if ($response->status == "user_already_exists") {
            $this->error(UserExists);
        } else if ($response->status == "blacklisted") {
            $this->error(UserBlacklisted);
        } else if ($response->status == "vpn_blocked") {
            $this->error(VPNBlocked);
        } else {
            return FALSE;
        }
    }

    function license($license)
    {
        if (!$_SESSION['initialized']) {
            die(NotInitialized);
        }

        $key = $this->randomString(32);
        $iv = $this->randomString(16);

        $data = array (
            "action" => base64_encode("license"),
            "userid" => base64_encode($this->userid),
            "app_name" => base64_encode($this->app_name),
            "secret" => $this->aes_encrypt($this->secret, $key, $iv),
            "license" => $this->aes_encrypt($license, $key, $iv),
            "hwid" => $this->aes_encrypt("123", $key, $iv), // your way of getting the users unique identifier
            "key" => $this->RSAEncrypt($key, $this->publicKey),
            "iv" => $this->RSAEncrypt($iv, $this->publicKey),
        );

        $response = json_decode($this->post($data));
        if($response->status == "ok") {
            $_SESSION['user_username'] = $this->aes_decrypt($response->username, $key, $iv);
            $_SESSION['user_email'] = $this->aes_decrypt($response->email, $key, $iv);
            $_SESSION['user_ip'] = $this->aes_decrypt($response->ip, $key, $iv);
            $_SESSION['user_expires'] = $this->aes_decrypt($response->expires, $key, $iv);
            $_SESSION['user_hwid'] = $this->aes_decrypt($response->hwid, $key, $iv);
            $_SESSION['user_last_login'] = $this->aes_decrypt($response->last_login, $key, $iv);
            $_SESSION['user_created_at'] = $this->aes_decrypt($response->created_at, $key, $iv);
            $_SESSION['user_variable'] = $this->aes_decrypt($response->variable, $key, $iv);
            $_SESSION['user_level'] = $this->aes_decrypt($response->level, $key, $iv);

            $app_variables = explode(";", $this->aes_decrypt($response->app_variables, $key, $iv));
            foreach ($app_variables as $app_variable) {
                $app_variable_split = explode(":", $app_variable);
                if (count($app_variable_split) == 2) {
                    $_SESSION['variables'][$app_variable_split[0]] = $app_variable_split[1];
                }
            }
            return TRUE;
        } else if ($response->status == "invalid_user") {
            $this->error(InvalidUserCredentials);
        } else if ($response->status == "user_limit_reached") {
            $this->error(UserLimitReached);
        } else if ($response->status == "invalid_license") {
            $this->error(RegisterInvalidLicense);
        } else if ($response->status == "license_expired") {
            $this->error(UserLicenseExpired);
        } else if ($response->status == "invalid_hwid") {
            $this->error(UserLicenseTaken);
        } else if ($response->status == "banned") {
            $this->error(UserBanned);
        } else if ($response->status == "license_taken") {
            $this->error(UserLicenseTaken);
        } else if ($response->status == "blacklisted") {
            $this->error(UserBlacklisted);
        } else if ($response->status == "vpn_blocked") {
            $this->error(VPNBlocked);
        }
        else {
            return FALSE;
        }
    }

    function add_log($username, $action)
    {
        if (!$_SESSION['initialized']) {
            die(NotInitialized);
        }

        $key = $this->randomString(32);
        $iv = $this->randomString(16);

        $data = array (
            "action" => base64_encode("log"),
            "userid" => base64_encode($this->userid),
            "app_name" => base64_encode($this->app_name),
            "secret" => $this->aes_encrypt($this->secret, $key, $iv),
            "username" => $this->aes_encrypt($username, $key, $iv),
            "user_action" => $this->aes_encrypt($action, $key, $iv),
            "key" => $this->RSAEncrypt($key, $this->publicKey),
            "iv" => $this->RSAEncrypt($iv, $this->publicKey),
        );

        $response = json_decode($this->post($data));
        if($response->status == "log_added") {
            return TRUE;
        } else if ($response->status == "failed") {
           $this->error(FailedToAddLog);
        } else if ($response->status == "invalid_log_info") {
            $this->error(InvalidLogInfo);
        } else if ($response->status == "log_limit_reached") {
            $this->error(LogLimitReached);
        }
    }

    function error($msg) {
        echo '<script type="text/JavaScript"> 
                Swal.fire({
                    title: "' . trim($msg) . '",
                    icon: "error",
                    confirmButtonText: "OK"
                })
            </script>';
    }

    function get_variable($secret) {
        if (!$_SESSION['initialized']) {
            die(NotInitialized);
        }

        try {
            return $_SESSION['variables'][$secret];
        } catch(Exception $e) { }
    }

    function post($data) {
        $curl = curl_init(endpoint);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function randomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function RSAEncrypt($input, $key) {
        $rsa = new \phpseclib\Crypt\RSA();
        $rsa->loadKey($key);
        $rsa->setEncryptionMode(\phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
        $encrypted = $rsa->encrypt($input);
        return base64_encode($encrypted);
    }

    function FormatPublicKey($publicKey) {
        $finalPublicKey = "-----BEGIN PUBLIC KEY-----\n";
        $chunks = preg_split('/(.{64})/', $publicKey, null, PREG_SPLIT_DELIM_CAPTURE);
    
        foreach ($chunks as $chunk) {
            $finalPublicKey .= $chunk . "\n";
        }
        $finalPublicKey .= "-----END PUBLIC KEY-----";
        
        return $finalPublicKey;
    }

    function aes_encrypt($input, $key, $iv) {
        $cipher = new \phpseclib\Crypt\AES();
        $cipher->setBlockLength(256);
        $cipher->setKey($key);
        $cipher->setIV($iv);
        $cipher->enablePadding();
        $enc = $cipher->encrypt($input);
        return base64_encode($enc);
    }

    function aes_decrypt($enc, $key, $iv) {
        $cipher = new \phpseclib\Crypt\AES();
        $cipher->setBlockLength(256);
        $cipher->setKey($key);
        $cipher->setIV($iv);
        $cipher->enablePadding();
        $enc = base64_decode($enc);
        return $cipher->decrypt($enc);
    }
}

?>