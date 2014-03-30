<?php
include_once("../includes/connection.php"); 

switch ($_SERVER['REQUEST_METHOD']) 
{
    case 'GET':
	    if (isset($_GET['setting']))
		{
	        //get the user information
            $res = mysql_query(sprintf("SELECT * FROM Setting WHERE u_id='%s'", mysql_real_escape_string($_GET["u_id"])))or die(mysql_error());
            $res = mysql_fetch_assoc($res);
		    if ($res)
		    {
		        header('Content-Type:application/json');
                echo json_encode($res);
			}
			else echo "NO Setting Found";
	    }
	    else if(isset($_GET['b_id']))
		{
		    //get the information of the reading information of the user who read a particular book
		    $res = mysql_query(sprintf("SELECT social_id FROM User WHERE u_id='%s'", mysql_real_escape_string($_GET["u_id"])))or die(mysql_error());
		    while ($row = mysql_fetch_assoc($res))
		    $array = $row;
            if (isset($array)) 
			{
                $res = mysql_query(sprintf("SELECT * FROM `Read` WHERE u_id='%s' and b_id='%s'", 
					                   mysql_real_escape_string($_GET["u_id"]),
									   mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
                $res = mysql_fetch_assoc($res);
				if($res)
				{
				     header('Content-Type:application/json');
                     echo json_encode($res);
			    }
				else echo "No READING HISTORY";
            }
            else echo "NO SUCH USER";
		}
		else if (isset($_GET['u_id']))
		{
	        //get the user information
            $res = mysql_query(sprintf("SELECT social_id FROM User WHERE u_id='%s'", mysql_real_escape_string($_GET["u_id"])))or die(mysql_error());
            while ($row = mysql_fetch_assoc($res))
		    $array['social_id'] = $row['social_id'];
		    if (isset($array))
		    {
			    $res = mysql_query(sprintf("SELECT * FROM Book b,`Read` r WHERE b.b_id=r.b_id and r.u_id='%s' ORDER BY r.lastTime DESC", mysql_real_escape_string($_GET["u_id"])))or die(mysql_error());
                while ($row = mysql_fetch_assoc($res))
				{
				    mysql_query(sprintf("UPDATE Book SET popularity=popularity+1 WHERE b_id='%s'",$row['b_id']))or die(mysql_error());
			        if ($row['cover_path']==NULL)
			        $b=array('type'=>0,'image'=>0);
			        else
			        {
			            $b_id=$row['b_id'];
				        $filename='../epubs/'.$b_id.'/'.$row['cover_path'];
			            $path=pathinfo('../epubs/'.$b_id.'/'.$row['cover_path']);
				        $filetype=$path['extension'];
                        if ($filename) 
			            {
                              $imgbinary = fread(fopen($filename, "r"), filesize($filename));
					          $base64 = chunk_split(base64_encode($imgbinary));
                              $b=array('type'=>$filetype,'image'=>$base64);
			            }
			         }
		             header('Content-Type:application/json');
				     $row=array_merge($b,$row);
					 $array2[] = $row;
				}
				if (isset($array2))
				$array['isreading']=$array2;
				else $array['isreading']=array();
		        header('Content-Type:application/json');
                echo json_encode($array);
			}
			else echo "NO SUCH USER";
	    }
       break;
    case 'POST':
	     if(isset($_POST['lastPosition']))
		 {  //create Read history
		    $b_id=$_POST['b_id'];
			$u_id=$_GET['u_id'];
			$res=mysql_query(sprintf("select * from `Read` where u_id='%s' and b_id='%s'",$u_id,$b_id))or die(mysql_error());
			$result = mysql_fetch_assoc($res);
			if ($result)
			{
				//if history already exists
				echo json_encode($result);
			}
			else
			{
			    //create new history
				$res = mysql_query(sprintf("insert into 
				`Read`
				values
				(
				'%s',
				'%s',
				1,
				CURRENT_TIMESTAMP,
				0
				)",
				$u_id,$b_id))or die(mysql_error());
				if($res)
				echo json_encode($res);//return true;
				else echo "FAIL TO INSERT";
			}
		 }
		else if(isset($_POST['social_id']))
		{
		    $s_id=mysql_real_escape_string($_POST["social_id"]);
		    $res = mysql_query("select u_id from `User` where social_id= '$s_id'")or die(mysql_error());
            $res = mysql_fetch_assoc($res);				
			if ($res)
			//exist user
			{
			    echo json_encode($res);
			}
			 else
            {			 
			  //create new user
	          $res = mysql_query("insert into `User`(social_id) values ('$s_id')")or die(mysql_error());	
			  if($res)
			  {
			     //create new user settings
				  $u_id=mysql_insert_id();
	              $res = mysql_query("insert into Setting values ($u_id,'Arial',14,'#000000','#f0f0f0')") or die(mysql_error());
			      if($res)
			      echo json_encode(array('u_id'=>$u_id));//return true;
			      else echo "FAIL TO INSERT";
			   }
			   else echo "FAIL TO INSERT";
			}
			
		}
	    break;
    case 'PUT':
         parse_str(file_get_contents('php://input'), $_PUT);
		 //update settings
		 if(isset($_PUT['lastPosition']))
		 {  //update Read history
		    $b_id=$_PUT['b_id'];
		    $lastPosition=mysql_real_escape_string($_PUT['lastPosition']);
		    $c_id=$_PUT['c_id'];
			$u_id=$_GET['u_id'];
			$res = mysql_query(sprintf("UPDATE `Read` SET 
			lastTime=CURRENT_TIMESTAMP,
			lastPosition='%s',
			c_id='%s' 
			where b_id='%s' and u_id='%s'",
			$lastPosition,$c_id,$b_id,$u_id))or die(mysql_error());
			if($res)
			echo json_encode($res);//return true;
			else echo "FAIL TO UPDATE";
		 }
		 else if(isset($_PUT['name']))
		 {
		    //update bookmark name
			$b_id=$_PUT['b_id'];
			$c_id=$_PUT['c_id'];
			$position=$_PUT['position'];
		    $name=$_PUT['name'];
			$res = mysql_query(sprintf("UPDATE `Bookmark` SET 
			name='%s', 
			where c_id='%s' and b_id='%s' and position='%s' and u_id='%s'",
			$name,$c_id,$b_id,$position,$_GET['u_id']))or die(mysql_error());
			if($res)
			echo json_encode($res);//return true;
			else echo "FAIL TO UPDATE";
		 }
		 else if(isset($_PUT['font']))
		 {
		    $font=$_PUT['font'];
			$font_size=$_PUT['font_size'];
			$text_color=$_PUT['text_color'];
			$bg_color=$_PUT['bg_color'];
			$u_id=$_GET['u_id'];
			$res = mysql_query(sprintf("UPDATE `Setting` SET font='%s',font_size='%s',text_color='%s',bg_color='%s' where u_id='%s'",
			$font,$font_size,$text_color,$bg_color,$u_id))or die(mysql_error());
			if($res)
			echo json_encode($res);//return true;
			else echo "FAIL TO UPDATE";
		 }
       break;
    case 'DELETE':
        parse_str(file_get_contents('php://input'), $_DELETE);
		if (check_authentication())
		{
			$res = mysql_query(sprintf("DELETE FROM `Read` WHERE b_id='%s' and u_id='%s' ",mysql_real_escape_string($_DELETE['b_id']),mysql_real_escape_string($_GET['u_id'])))or die(mysql_error());
			if($res)
			echo json_encode($res);//return true;
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