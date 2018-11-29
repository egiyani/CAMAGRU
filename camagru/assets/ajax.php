<?php

include ("../config/setup.php");

function recovery($email, $code)
{
	$subject 	= "Password Recovery for Born2Selfie";
	$from 		= "egiyani@student.wethinkcode.co.za";
	$body 		= "Hi," . "\nyour Recovery Code is the following : " . $code . "\nIf you didn't request a Recovery Code, please ignore this message.";
	$headers 	= 'From:' . $from;
	mail($email,$subject,$body,$headers);
}

function recoverPassword($email, $password, $db)
{
	$found 		= "email";

	$DBemail 	= $db->quote($email);
	$DBpassword = $db->quote(hash("whirlpool", $password));

	$select 	= $db->query("SELECT * FROM users WHERE email=$DBemail");
	$ret 		= $select->rowCount();
	if ($ret != 0) {
		$found 	= "found";
		$db->query("UPDATE users
			SET password=$DBpassword
			WHERE email=$DBemail");
	}
	return ($found);
}

$email 		= $_POST['email'];
$code 		= $_POST['code'];
$action 	= $_POST['action'];
$password 	= $_POST['password'];

if ($action === "mailCode")
	recovery($email, $code);
if ($action === "changePass")
{
	$pwdCheck = TRUE;
	if (strlen($password) < 8)
	{
		$pwdCheck = FALSE;
		$return = "len";
	}
	if (!preg_match("#[0-9]+#", $password))
	{
		$pwdCheck = FALSE;
		$return = "nbr";
    }
    if (!preg_match("#[a-zA-Z]+#", $password))
	{
    	$pwdCheck = FALSE;
    	$return = "alpha";
    }
	if ($pwdCheck == TRUE)
		$return = recoverPassword($email, $password, $db);
}

echo $return;

?>