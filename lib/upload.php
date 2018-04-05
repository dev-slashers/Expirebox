<?php
include 'account.php';
class File
{
	function Verifica_Estensione($file)
	{
		global $estensione_file;
		$verifica = false;
		$file = end(explode(".",$file));
		foreach($estensione_file as $ex)
		{
			if($file == $ex) $verifica = true;
		}
		return $verifica;
	}
	function Peso($size)
	{ 
		if($size < 1000000)
		{ 
			$size = ceil($size/1024). " KB"; 
		}else
		{ 
			$size = round(($size/1024)/1024,1)." MB"; 
		} 
		return $size; 
	}
	
	function Controllo_file_in_database($filename)
	{
		global $DBprefix,$mysqli;
		$verifica = false;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE nome LIKE '".$filename."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		if($row['nome'] != $filename) $verifica = true;
		$result->close();
		return $verifica;
	}
	
	function Upload()
	{
		global $limite_download,$dirupload,$DBprefix,$mysqli,$sitehost;
		$account = new Account();
		$f = new File();
		$caratteri_non_accettati = array("$"," ","^","\\","$","*","+","<",">","%","?","/");
		$filename = str_replace($caratteri_non_accettati,"_",$_FILES['gfile']['name']);
		$delete_key = strtoupper(md5($account->StrRandom()));
		$code_download = strtoupper(substr($account->StrRandom(),0,8));
		$public_file = $_POST['public_file'] == 'on' ? 1 : 0;
		if($account->isLogin() == false && $public_file == 0) $public_file = 1;
		if(isset($_POST['dati']))
		{
			echo "<body onLoad='Nascondi_c()' />";
			if($f->Verifica_Estensione($filename) == true && $f->Controllo_file_in_database($_FILES['gfile']['name']) == true)
			{
				move_uploaded_file($_FILES['gfile']['tmp_name'], $dirupload.$filename);
				$query = sprintf("INSERT INTO ".$DBprefix."host (url, code,limite,numero_download,dimensioni,nome,commento,delete_key,nickname,public) VALUES ('%s', '%s','%s','%s', '%s','%s','%s', '%s','%s','%s')",$dirupload.$filename, $code_download,$_POST['limite'],0,$f->Peso($_FILES['gfile']['size']),$filename,$_POST['commento'],$delete_key,$account->GetInfo(1),$public_file);
				$mysqli->query($query);
				echo "<b>Download:</b> <input type='text' size='60' readonly='readonly' value='".$sitehost."download.php?id=".$code_download."' /> <br>
				<b>Elimina:</b> <input type='text' size='60' readonly='readonly' value='".$sitehost."?i=delete&id=".$delete_key."'> <br><br><br><br>";
			}else
			{	$ErrorList = "";
				if($f->Verifica_Estensione($filename) == false) $ErrorList .= "<li> Estensione non consentita <br>";
				if($f->Controllo_file_in_database($_FILES['gfile']['name']) == false) $ErrorList .= "<li> File già presente nel database <br>";
				echo $ErrorList;
			}
		}
		$file_pubblico = $account->isLogin() == true ? 'Pubblico: <input name="public_file" type="checkbox" checked="checked"/>':'';
		echo '<form method="POST" action="?i=home" enctype="multipart/form-data">
		<input type="file" name="gfile" /> <br> <textarea class="smusso" name="commento" placeholder="Commento" maxlength="100" cols= 35 rows=3></textarea> <br>
		Download:<select name="limite">
		';
		for($i = 1;$i <= $limite_download;$i++)
		{
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
		echo '
		<option value="MAX">Illimitato</option>
		</select><br>
		'.$file_pubblico.'<br> <input type="submit" name="dati" class="Pulsante" onclick="Mostra_c()" value="Carica" />
		</form>';
	}
}
?>