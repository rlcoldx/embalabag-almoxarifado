<?php
	$root = dirname(dirname(dirname(__DIR__)));
	include($root . '/config/db.php');
	include($root . '/config/config.php');

	if (isset($_POST['file'])) {

		$sql_item = $db->prepare("SELECT * FROM produtos_imagens WHERE `id_produto` = '".$_GET['id_produto']."' AND nome = '".$_POST['file']."'");
		$sql_item->execute();
		$item = $sql_item->fetch(PDO::FETCH_ASSOC);

		$caminho = explode('-', $item['data']);
		$nome_arquivo_thumb = explode('.', $item['nome']);

		$diretorio = '../../uploads/produtos/'.$caminho[0].'/'.$caminho[1].'/';
		$diretorio_thumbnail = '../../uploads/produtos_thumbnail/'.$caminho[0].'/'.$caminho[1].'/';

		$file = $diretorio.$item['nome'];
		$file_thumbnail = $diretorio_thumbnail.$nome_arquivo_thumb[0].'.jpg';
		
		if(file_exists($file))
			unlink($file);
			unlink($file_thumbnail);

		$sql = $db->prepare("DELETE FROM `produtos_imagens` WHERE `id_produto` = '".$_GET['id_produto']."' AND id = '".$item['id']."'");
		$sql->execute();

	}

	echo $file;