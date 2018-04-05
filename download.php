<?php
include 'lib/config.php';
class Edit
{

	function GetNick()
	{
		global $mysqli,$DBprefix;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			if($_COOKIE[$row['nickname']] == $row['password'] && $row['activate'] == 1) 
			{
				$nickname = $row['nickname'];
			}
		}
		$result->close();
		return $nickname;
	}
	function Elimina_file($id)
	{
		global $mysqli,$DBprefix;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE id LIKE '".$id."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$file_url = $row['url'];
		$result->close();
		$mysqli->query("DELETE FROM ".$DBprefix."host WHERE id LIKE '".$id."'");
		$mysqli->close();
		if(file_exists($file_url)) unlink($file_url);
	}

	function Download($id)
	{
		global $mysqli,$DBprefix,$AdminUsr;
		if(preg_match("/^[a-zA-Z0-9]*$/", htmlentities($id)) && $id != "")
		{
			$e = new Edit();
			$nick = $e->GetNick();
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE code LIKE '".$id."'", MYSQLI_USE_RESULT);
			$download = false;
			$limite = false;
			$row = $result->fetch_assoc();
			if($id == $row['code'])
			{
				$nome = $row['url'];
				$n_download = $row['numero_download'] + 1;
				$id_file = $row['id'];
				
				if($row['public'] == 0)
				{
					if($row['nickname'] == $nick || $nick == $AdminUsr)
					{
						$download = true;
					}
				}if($row['public'] == 1)
				{
					$download = true;
				}
				if($row['numero_download'] >= $row['limite']) $limite = true;
			}
		
	
			$result->close();
			if($limite == true) $e->Elimina_File($id_file);
			if($download == true && $limite == false)
			{
				$mysqli->query("UPDATE ".$DBprefix."host SET numero_download = '".$n_download."' WHERE id=".$id_file);//incremento il numero di download
				$mysqli->close();
				Header("Content-type: application/octet-stream");
				Header("Content-Disposition: attachment; filename=".$nome);
				Header("Content-Description: Download Manager");
				Header("Pragma: No-Cache");
				Header("Expires: 0");
				Header("Content-Length:".filesize($nome));
				readfile($nome);
			}
		}else
		{
			header('Location: index.php');
		}
	}
}

$edit = new Edit();
$edit->Download($_GET['id']);
?>