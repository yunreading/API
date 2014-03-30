<?php
include_once("../includes/connection.php"); 
//get the user information
$content=$_GET['content'];
//$res = mysql_query(sprintf("SELECT * FROM `Book` WHERE title like '%s%' or author like '%s%' or publisher like '%s' ", mysql_real_escape_string($_GET['content']),mysql_real_escape_string($_GET['content']),mysql_real_escape_string($_GET['content'])))or die(mysql_error());
$res = mysql_query("SELECT * FROM `Book` WHERE title like '%".$content."%' or author like '%".$content."%' or publisher like '%".$content."%'")or die(mysql_error());
while ($row = mysql_fetch_array($res))
{
   $result[]=$row;
}
if (isset($result))
{
    header('Content-Type:application/json');
    echo json_encode($result);
}
else echo "NO BOOK FOUND";	      
?>