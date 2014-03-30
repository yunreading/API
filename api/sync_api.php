<?php
 include_once("../includes/connection.php"); 
 switch($_GET['synctype'])
 {
     case 'bookmark':
		//$knownBookmarks = array();
		//for($_POST['bm_list'] as $bookmark){
		//	echo(isset($_POST['bm_list']));
		$empty = 0;
		if(strlen($_POST['bm_list']) <= 0)
			$empty = 1;
			
		for($i=0; $empty==0&&$i<count($_POST['bm_list']); $i++){
			echo $empty;
			$bookmark = $_POST['bm_list'][$i];
			if($bookmark['markID']<=0){
				$res = mysql_query(sprintf("INSERT INTO `Bookmark`(u_id,b_id,c_id,name,position) VALUES  
					('%s','%s','%s','%s','%s')",
					$_POST['u_id'],$bookmark['bookID'],$bookmark['chapterID'],$bookmark['name'],$bookmark['position'])) or die(mysql_error());
				//$knownBookmarks[] = mysql_insert_id();
			} else {
				$knownBookmarks[] = $bookmark['markID'];
				$res = mysql_query(sprintf("UPDATE `Bookmark` SET name='{$bookmark['name']}' WHERE 
									bm_id='{$bookmark['markID']}'"));
				if(!$res){
					die("Insert bookmarks: ".mysql_error());
				}
			}
		}
		
		$empty = 0;
		if(strlen($_POST['delete_list']) <= 0)
			$empty = 1;
		$query = "DELETE FROM `Bookmark` WHERE";
		$deleteList = $_POST['delete_list'];
		for($i=0;$empty==0&&$i<count($_POST['delete_list']);$i++)
		{
			if($i!=0) $query.=" OR";
			$query.=" bm_id ='".$deleteList[$i];
		}
		if($empty==0)
		{
			$res = mysql_query($query);
			if(!$res){
				die("Delete bookmark: ".mysql_error());
			}
		}
	/*	if(count($knownBookmarks)>0){
			$query = "DELETE FROM `Bookmark` WHERE";
			for($i=0; $i<count($knownBookmarks); $i++){
				if($i!=0) $query.=" AND";
				$query.=" bm_id<>'".$knownBookmarks[$i]."' AND u_id= '$_POST['u_id']'";
			}
			$res = mysql_query($query);
			if(!$res){
				die("Delete bookmark: ".mysql_error());
			}
		}*/
		break;
	 case 'history':
		$u_id = $_POST['u_id'];
		$history = $_POST['history'];
		$empty = 0;
		if(strlen($_POST['history']) <= 0)
			$empty = 1;
		for($i=0; $empty==0&&$i<count($history); $i++){

			$res = mysql_query("UPDATE `Read` SET lastTime='{$history['lastTime']}',
								lastPosition='{$history['lastPosition']}',
								c_id='{$history['chapterID']}' WHERE 
								b_id='{$history['b_id']}' AND 
								u_id='{$u_id}' AND 
								lastTime<{$history['lastTime']}");
			if(!$res){
				die("Error update history: ".mysql_error());
			}
		}
		echo json_encode(array());
		break;
	 case 'settings':
		$font=$_POST['font'];
		$font_size=$_POST['font_size'];
		$text_color=$_POST['text_color'];
		$bg_color=$_POST['bg_color'];
		$u_id=$_POST['u_id'];
		$res = mysql_query(sprintf("UPDATE `Setting` SET font='%s',font_size='%s',text_color='%s',bg_color='%s' where u_id='%s'",
		$font,$font_size,$text_color,$bg_color,$u_id))or die(mysql_error());
		if($res)
		echo json_encode($res);//return true;
		else echo "FAIL TO UPDATE";
		  break;
 }
?>
