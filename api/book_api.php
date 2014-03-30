<?php
include_once("../includes/connection.php");
include_once("htmldom.php");

function file_convert_html($path,$bookID){
	$content = file_get_html($path);
	foreach($content->find('img') as $element){
		$element->src='http://www.yunreading.com/epubs/'.$bookID.'/'.$element->src;
	}
	$content = sprintf("%s",$content);
	return $content;
}

switch ($_SERVER['REQUEST_METHOD']) 
{
	case 'GET':
		if(isset($_GET['num']))
		{
			$num=$_GET['num'];
			//get the previous/next num of particular chapters
			$res = mysql_query(sprintf("SELECT * FROM Book WHERE b_id='%s'", mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
			while ($row = mysql_fetch_assoc($res))
				$array = $row;
			if (isset($array)) 
			{
				$res = mysql_query(sprintf("SELECT c_id, filename FROM Chapter WHERE b_id='%s' and (c_id<='%s' or c_id>='%s')", 
							mysql_real_escape_string($_GET["b_id"]),
							mysql_real_escape_string($_GET["c_id"]+$num),
							mysql_real_escape_string($_GET["c_id"]-$num)))or die(mysql_error());
				while ($row = mysql_fetch_assoc($res))
					$array2[] = $row; 
				$b_id=$_GET["b_id"];
				if (isset($array2)) 
				{
					$num=count($array2);
					for($i=0; $i<$num; $i++)
					{
						header('Content-Type: text/html; charset=utf-8');
						//$size=$array2[0]['size'];
						$content= file_convert_html('../epubs/'.$b_id.'/'.$array2[$i]['filename'],$b_id);
						$book[]=array('c_id'=>$array2[$i]['c_id'],'content'=>$content);
					}
					header('Content-Type:application/json');
					echo json_encode($book);
				}
				else echo "NO SUCH CHAPTER";
			}
			else echo "NO SUCH BOOK";
		}
		else if(isset($_GET['c_id']))
		{
			//get the information of a particular chapter
			$res = mysql_query(sprintf("SELECT language FROM Book WHERE b_id='%s'", mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
			while ($row = mysql_fetch_assoc($res))
				$array = $row;
			if (isset($array)) 
			{
				if ($_GET['c_id']==0)
				{
					$res = mysql_query(sprintf("SELECT c_id FROM Chapter WHERE b_id='%s'", 
								mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
					while ($row = mysql_fetch_assoc($res))
						$array2[] = $row; 
					if (isset($array2)) 
					{
						header('Content-Type:application/json');
						echo json_encode($array2);
					}
					else echo "NO SUCH CHAPTER";
				}
				else
				{
					$res = mysql_query(sprintf("SELECT filename FROM Chapter WHERE b_id='%s' and c_id='%s'", 
								mysql_real_escape_string($_GET["b_id"]),
								mysql_real_escape_string($_GET["c_id"])))or die(mysql_error());
					while ($row = mysql_fetch_assoc($res))
						$array2[] = $row; 
					$b_id=$_GET["b_id"];
					if (isset($array2)) 
					{ 
						$charset = "charset=utf-8";
						$mime = "text/html";
						$ext = substr(strrchr($array2[0]['filename'],'.'),1);
						if($ext=="xhtml")
							$mime = "application/xhtml+xml";
						header("Content-Type: $mime;charset=$charset");
						header("Vary: Accept");

						$content= file_convert_html('../epubs/'.$b_id.'/'.$array2[0]['filename'],$b_id);

						$book=array('content'=>$content,'language'=>$array['language']);
						header('Content-Type:application/json');
						echo json_encode($book);
					}
					else echo "NO SUCH CHAPTER";
				}
			}
			else echo "NO SUCH BOOK";
		}
		else if(isset($_GET['cover']))
		{
			$res = mysql_query(sprintf("SELECT cover_path FROM Book WHERE b_id='%s'", mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
			$row = mysql_fetch_array($res);
			if (isset($row))
			{
				if ($row['cover_path']==NULL)
					$url='http://yunreading.com/styles/images/large.png';
				else
				{    
					$b_id=$_GET["b_id"];
					$url='http://www.yunreading.com/epubs/'.$b_id.'/'.$row['cover_path'];
				}
				header("Content-Type:image/*"); 
				header( "Location: $url" );
			}
		}
		else if(isset($_GET['title']))
		{
			$res = mysql_query(sprintf("SELECT title FROM Book WHERE b_id='%s'", mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
			$row = mysql_fetch_array($res);
			if (isset($row))
			{
				echo json_encode($row);
			}
		}
		else if(isset($_GET['b_id']))
		{
			//get the general book information
			$res = mysql_query(sprintf("SELECT * FROM Book WHERE b_id='%s'", mysql_real_escape_string($_GET["b_id"])))or die(mysql_error());
			$row = mysql_fetch_array($res);
			if ($row)
			{
				if ($row['cover_path']==NULL)
					$b=array('type'=>0,'image'=>0);
				else
				{

					$b_id=$_GET["b_id"];
					$filename='../epubs/'.$b_id.'/'.$row['cover_path'];
					$path=pathinfo('../epubs/'.$b_id.'/'.$row['cover_path']);
					$filetype=$path['extension'];
					if ($filename) 
					{
						$imgbinary = file_get_contents($filename);
						$base64 = chunk_split(base64_encode($imgbinary));
						$b=array('type'=>$filetype,'image'=>$base64);
					}
				}
				header('Content-Type:application/json');
				$b=array_merge($b,$row);
				echo json_encode($b);
			}
			else echo "NO SUCH BOOK";
		}
		break;
	case 'POST':
	case 'PUT':

		break;
	case 'DELETE':
		parse_str(file_get_contents('php://input'), $_DELETE);
		if (check_authentication())
		{
			$res = mysql_query(sprintf("DELETE FROM Book WHERE b_id='%s'",mysql_real_escape_string($_GET['b_id'])))or die(mysql_error());
			echo json_encode($res);
		}
		else 
			echo "unauthorized";
		break;
}
function check_authentication()
{
	return false;
}
?>
