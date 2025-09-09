<?php
	$root = dirname(dirname(dirname(__DIR__)));
	include($root . '/config/db.php');
	include($root . '/config/config.php');

    $list = isset($_POST['_list']) ? json_decode($_POST['_list'], true) : null;
   
	foreach ($list as $key => $item){

		$sql = $db->prepare("UPDATE `banners` SET `order` = '".$item['index']."' WHERE `local` = '".$_GET['id']."' AND nome = '".$item['name']."'");
		$sql->execute();
		
	}