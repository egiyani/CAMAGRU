<?php

include("../config/setup.php");

function vMail($username, $email, $vHash) {
	$salt = "a42b8over9000";
	$token = hash("whirlpool", $vHash . $salt);
	$subject = "Email Verification for Camagru";
	$from = "no-reply@camagru.com";
	$body = "Hi " . $username . ",\nthank you for registering at Camagru ! To log in on our site, please verify your email by clicking on the link below.\nhttp://127.0.0.1:8080/camagru/auth/verification?email=" . $email . "&token=" . $token;
	$headers = 'From:' . $from;
	mail($email,$subject,$body,$headers);
}

function register($username, $email, $password, $db) {
	$serialized_file = "../private/users";
	$DBusername = $db->quote($username);
	$DBemail = $db->quote($email);
	$DBpassword = $db->quote(hash("whirlpool", $password));

	$select = $db->query("SELECT * FROM users WHERE username=$DBusername");
	if ($select->rowCount() != 0)
		$_SESSION['regInfo'] = "username";
	$select = $db->query("SELECT * FROM users WHERE email=$DBemail");
	if ($select->rowCount() != 0)
		$_SESSION['regInfo'] = "email";
	if (!isset($_SESSION['regInfo'])) {
		$vHash = hash("whirlpool", rand(0, 1000));
		$db->query("INSERT INTO users SET username=$DBusername, password=$DBpassword, email=$DBemail, vHash='$vHash'");
		$_SESSION['logInfo'] = "registered";
		vMail($username, $email, $vHash);
	}
}

function login($username, $password, $db) {
	$DBusername = $db->quote($username);
	$hashed = hash("whirlpool", $password);
	$DBpassword = $db->quote(hash("whirlpool", $password));

	$select = $db->query("SELECT * FROM users WHERE username=$DBusername AND password=$DBpassword");
	$res = $select->rowCount();
	if ($res != 0) {
		$_SESSION['user'] = $username;
		$select = $db->query("SELECT * FROM users WHERE username=$DBusername AND password=$DBpassword AND verified=0");
		$res = $select->rowCount();
		if ($res != 0) {
			unset($_SESSION['user']);
			$_SESSION['logInfo'] = "verification";
		}
	}
	else
		$_SESSION['logInfo'] = "credentials";
	if (!isset($_SESSION['user']) && $_SESSION['logInfo'] !== "verification")
		$_SESSION['logInfo'] = "credentials";
}

session_start();

$auth = $_POST;
if ($auth['registerSubmit'] && $auth['username'] && $auth['email'] && $auth['passwd'])
{
	$pwdcheck = TRUE;
	if (strlen($auth['passwd']) < 8)
	{
		$pwdcheck = FALSE;
		$_SESSION['regPass']['len'] = TRUE; 
	}
	if (!preg_match("#[0-9]+#", $auth['passwd']))
	{
		$pwdcheck = FALSE;
        $_SESSION['regPass']['nbr'] = TRUE;
    }
    if (!preg_match("#[a-zA-Z]+#", $auth['passwd']))
	{
    	$pwdcheck = FALSE;
        $_SESSION['regPass']['alpha'] = TRUE;
    }
	if ($pwdcheck == TRUE)
		register($auth['username'], $auth['email'], $auth['passwd'], $db);
}
else if ($auth['loginSubmit'] && $auth['username'] && $auth['passwd'])
	login($auth['username'], $auth['passwd'], $db);

if ($_SESSION['regPass']['len'] == FALSE || $_SESSION['regPass']['nbr'] == FALSE || $_SESSION['regPass']['alpha'] == FALSE)
	header("Location: register");
if (isset($_SESSION['user']) && !isset($_SESSION['logInfo']))
	header("Location: ../");
if (isset($_SESSION['logInfo']))
	header("Location: login");
if (isset($_SESSION['regInfo']))
	header("Location: register");

?>