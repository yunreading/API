<?php 
     if(isset($_GET['path']))
	{
		$url='http://www.yunreading.com/epubs/'.$b_id.'/'.$_GET['path'];
		header( "Location: $url" );
	}
?>