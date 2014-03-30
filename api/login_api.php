<?php
include_once("../includes/connection.php"); 

switch ($_SERVER['REQUEST_METHOD']) 
{
    case 'POST':
	    $email=$_POST["email"];
		$password=$_POST["password"];
		$res = mysql_query("SELECT * FROM `User` WHERE email= '$email' AND password='$password'")or die(mysql_error());	
		if (mysql_num_rows($res)<=0)
		echo "Error";
		else
		{
		    $profile=mysql_fetch_array($res);
			echo json_encode($profile);
		}
	    break;
}
?>