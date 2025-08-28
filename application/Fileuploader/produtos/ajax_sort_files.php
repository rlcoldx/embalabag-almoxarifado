<?php
	include('../../config/db.php');

    $list = isset($_POST['_list']) ? json_decode($_POST['_list'], true) : null;
   
	foreach ($list as $key => $item){

		$sql = $db->prepare("UPDATE `produtos_imagens` SET `order` = '".$item['index']."' WHERE `id_produto` = '".$_GET['id_produto']."' AND nome = '".$item['name']."'");
		$sql->execute();
		
	}