<?php
include_once("../includes/connection.php"); 

switch ($_SERVER['REQUEST_METHOD']) 
{
    case 'POST':
	    $email=$_POST["email"];
		$password=$_POST["password"];
		$name=$_POST["name"];
		$res = mysql_query("SELECT * FROM `User` WHERE email= '$email'")or die(mysql_error());	
		if (mysql_num_rows($res)>0)
		echo "Error";
		else
		{
			$res = mysql_query("insert into `User`(email,password,name) values ('$email','$password','$name')")or die(mysql_error());	
			if($res)
			{
					 //create new user settings
					  $u_id=mysql_insert_id();
					  $res = mysql_query("insert into Setting values ($u_id,'Arial',14,'#000000','#f0f0f0')") or die(mysql_error());
					  if($res)
					  echo json_encode(array('u_id'=>$u_id));//return true;
					  else echo "FAIL TO INSERT";
			}
			else echo "Error";
		}
	    break;
}
?>