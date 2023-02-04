
<h1 align="center">
  <br>
  <a href="https://authspire.com"><img src="https://i.ibb.co/KxvFZ5B/logo.png" alt="AuthSpire" width="200"></a>
  <br>
  AuthSpire
  <br>
</h1>

<h4 align="center">A FREE and secure licensing & authentication solution<br>using hybrid encryption.</h4>

<p align="center">
  <a href="#key-features">Key Features</a> •
  <a href="#how-to-use">How To Use</a> •
  <a href="#functions">API Functions</a> •
</p>

<div align="center">
    <img src="https://media.giphy.com/media/V6v60D0r4St0xXNJqo/giphy.gif" width="450"> 
</div>


## Key Features

* License your software / application
  - Restrict access from other users and increase security
* Manage Users
  - See who uses your application, set expiry dates for your licenses & more
* Variables
  - Set custom hidden variables that are secured on our server and can not be cracked
* Blacklists
  - Block users by IP or a Unique Identifier from accessing your application
* Logging
  - Handle all logs and see what is happening inside of your application
* Hybrid Encryption System
  - Encryption combined using AES 256 (Advanced Encryption Standard) and RSA to ensure the most security

## How To Use

This piece of code uses <a href="https://github.com/phpseclib/phpseclib">phpseclib</a>. The installation steps
can be found on their page.

Create an account on the <a href="https://authspire.com/sign-up">AuthSpire</a> website.
Create your application.
<br>
<br>
Name: Name of your application in the dashboard<br>
UserID: UserID found in your account page<br>
Secret: Secret of your application in the dashboard<br>
Version: Version 1.0 by default (for updates change the version accordingly)<br>
Public Key: Public Key for encryption found in the dashboard<br>
<br>

```php
<?php
  $app_name = "";     // Name of your application found in the dashboard
  $userid = "";      // ixwupMLC Your userid can be found in your account settings.
  $secret = ""; // Application secret found in the dashboard
  $currentVersion = "1.0"; // Current application version.
  $publicKey = ""; // Your public key for encryption.
?>
```


## Functions

<b>Initializing your application</b>

Before using any other functions it is necessary to initialize your application with our server and retrieve all data.
This can be done by calling this method in your main index.php file.

```php
<?php
error_reporting(0);

include 'authspire.php';
include 'details.php';

session_start();

$authSpireAPI = new authSpire\api($app_name, $userid, $secret, $currentVersion, $publicKey);


if(!$_SESSION['initialized'])
{
  $authSpireAPI->init();
}

?>
```

<b>Register a user</b>

To register and add a new user to your application you will first require a valid license key which you can generate in 
your authspire dashboard in your selected application.

Register a user by calling this method and validate the registration

```php
$authSpireAPI->register("username", "password", "license", "email");
```

<b>Authenticate a user</b>

To login and add retrieve all user data you can call this method

```php
$authSpireAPI->login("username", "password");
```

<b>Adding logs</b>

Sometimes it is necessary to have an overview of what is going on inside your application. For that you can use logs

To add a log you can call this method.

```php
$authSpireAPI->add_log("username", "action");
```

<b>Getting Variables</b>

You can store server-sided strings. Your application can then retrieve these strings with a secret key that will be generated in your panel
when you generate your variable. This protects your strings from being decompiled or cracked.

```php
$authSpireAPI->get_variable("secret");
```

<b>Authenticate with only a license</b>

Sometimes you want to keep it simple. A user can register/login with only using a license. For this you can use this function

```php
$authSpireAPI->license("license");
```

## License

MIT
