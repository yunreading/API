<?php
include_once("../includes/connection.php"); 

switch ($_SERVER['REQUEST_METHOD']) 
{
    case 'GET':
	    if(isset($_GET['bm_id']))
		{
		$bm_id=$_GET['bm_id'];
	    if(isset($_GET['b_id']))
		{
		    //get the information of the reading information of the user who read a particular book
		    $res = mysql_query(sprintf("SELECT * FROM `User` WHERE u_id='%s'", mysql_real_escape_string($_GET["u_id"])))or die(mysql_error());
		    while ($row = mysql_fetch_assoc($res))
		    $array = $row;
            if (isset($array)) 
			{
			        if ($bm_id==0)
					{
				        $res = mysql_query(sprintf("SELECT * FROM `Bookmark` WHERE u_id='%s' and b_id='%s'", 
					                   mysql_real_escape_string($_GET["u_id"]),
									   mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
						$bm=array();
                        while ($row = mysql_fetch_assoc($res))
			     	   {
						   $bm[]=$row;
				       }
				        header('Content-Type:application/json');
                        echo json_encode($bm);
					}
					else
					{
					    $res = mysql_query(sprintf("SELECT * FROM `Bookmark` WHERE bm_id='%s'", 
									   mysql_real_escape_string($_GET["bm_id"])))or die(mysql_error());
						if($row)
                        {
				           header('Content-Type:application/json');
                           echo json_encode($row);
						}
						else
						echo "0";
					}
            }
            else echo "NO SUCH USER";
		}
		}
       break;
    case 'POST':
	     mysql_query("SET character_set_client=utf8");
	     mysql_query("SET character_set_connection=utf8");
	     if(isset($_POST['name']))
		 {
		    //update bookmark name
			$u_id=$_POST['u_id'];
			$b_id=$_POST['b_id'];
			$c_id=$_POST['c_id'];
			$position=$_POST['position'];
		    $name=$_POST['name'];
			$res = mysql_query(sprintf("insert into `Bookmark`
			(
			u_id,
			b_id,
			c_id,
			name,
			position
			)
			values
			(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
			)",
			$u_id,$b_id,$c_id,$name,$position))or die(mysql_error());
			if($res)
			{
			    echo json_encode(mysql_insert_id());
			}
			else echo "FAIL TO INSERT";
		 }
    case 'PUT':
        if(isset($_PUT['name']))
		 {
		    //update bookmark name
		    $name=$_PUT['name'];
			$res = mysql_query(sprintf("UPDATE 'Bookmark' SET 
			name='%s', 
			where bm_id='%s'",
			$name,$_GET['bm_id']))or die(mysql_error());
			if($res)
			echo json_encode($res);//return true;
			else echo "FAIL TO UPDATE";
		 }
       break;
    case 'DELETE':
        parse_str(file_get_contents('php://input'), $_DELETE);
		if (check_authentication())
		{
			$res = mysql_query(sprintf("DELETE FROM Bookmark WHERE bm_id='%s'",mysql_real_escape_string($_GET['bm_id'])))or die(mysql_error());
			if($res) echo json_encode($res);//return true;
			else echo "FAIL TO DELETE";
		}
		else 
			echo "UNAUTHORIZED";
        break;
}
function check_authentication()
{
return true;
}
?>
