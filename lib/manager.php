<?php
include 'upload.php';
class Search
{
	function Verifico_Limite()
	{
		global $mysqli,$DBprefix;
		$lista_id = array();
		$lista_url = array();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."host", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			if($row['numero_download'] >= $row['limite'])
			{
				$lista_id[] = $row['id'];
				$lista_url[] = $row['url'];
			}
		}
		$result->close();
		foreach($lista_id as $item)
		{
			$mysqli->query("DELETE FROM ".$DBprefix."host WHERE id LIKE '".$item."'");
			
		}
		foreach($lista_url as $item)
		{
			if(file_exists($item)) unlink($item);
		}
	}
	
	function Private_Download()
	{
		global $mysqli,$DBprefix,$sitehost;
		$account = new Account();
		if($account->isLogin() == true)
		{

			$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE nickname LIKE '".$account->GetInfo(1)."'", MYSQLI_USE_RESULT);
			echo '					<table class="tabella" border="1" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
			<td width="31%">
			<p class="tabella_caratteri"><b>Nome</b></td>
			<td width="30%">
			<p class="tabella_caratteri"><b>Link</b></td>
			<td width="18%">
			<p class="tabella_caratteri"><b>Dimensioni</b></td>
			<td width="8%">
			<p class="tabella_caratteri"><b>Elimina</b></td>
			<td width="38%">
			<p class="tabella_caratteri"><b>Download Disponibili</b></td>
		  </tr>';
			while($row = $result->fetch_assoc())
			{
				$download_effettuati = $row['limite'] == 'MAX' ? "<img src='img/infinity.png' heigh='20' width='20' />":($row['limite'] - $row['numero_download']);
				$commento = $row['commento'] != '' ? "alert('".$row['commento']."')" : "alert('Nessun Commento')";
				$download = $sitehost.'download.php?id='.$row['code'];
				$delete_ = $sitehost."?i=delete&id=".$row['delete_key'];
				echo '	  
				<tr>
				<td align="center" width="31%"><a href="javascript:'.$commento.'">'.$row['nome'].'</a></td>
				<td align="center" width="30%"><a href="'.$download.'">'.$download.'</a></td>
				<td align="center" width="18%">'.$row['dimensioni'].'</td>
				<td align="center" width="8%"><a href="'.$delete_.'"><img src="img/delete.png" /></a></td>
				<td align="center" width="38%">'.$download_effettuati.'</td>
				</tr>';
			}
			echo '</table>';
			$result->close();
		}
	}
	function Public_Download()
	{
		global $mysqli,$DBprefix,$sitehost,$AdminUsr;
		$account = new Account();
		$nick_ = $account->GetInfo(1);
		if($_POST['key_search'] != '')
		{
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE nome LIKE '%".$_POST['key_search']."%'", MYSQLI_USE_RESULT);
		}else
		{
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."host ORDER BY  `".$DBprefix."host`.`id` DESC", MYSQLI_USE_RESULT);
		}
		echo '<form method="POST" action="?i=search">
<input class="cerca" name="key_search" id="area_testo" type="text" placeholder="Cerca" onClick="ReSize()" onBlur="ReSize()" size="10">
</form>
		<table class="tabella" border="1" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
			<td width="25%">
			<p class="tabella_caratteri"><b>Nome</b></td>
			<td width="25%">
			<p class="tabella_caratteri"><b>Link</b></td>
			<td width="25%">
			<p class="tabella_caratteri"><b>Dimensioni</b></td>
			<td width="25%">
			<p class="tabella_caratteri"><b>Download Disponibili</b></td>
		  </tr>';
		while($row = $result->fetch_assoc())
		{
			$download = $sitehost.'download.php?id='.$row['code'];
			$download_effettuati = $row['limite'] == 'MAX' ? "<img src='img/infinity.png' heigh='20' width='20' />":($row['limite'] - $row['numero_download']);
			$commento = $row['commento'] != '' ? "alert('".$row['commento']."')" : "alert('Nessun Commento')";
			if($nick_ != $AdminUsr)
			{
				if($row['public'] == 0 && $row['nickname'] != $nick_ && $row['nickname'] != '') continue;
			}
				echo '
				<tr>
				<td align="center" width="25%"><a href="javascript:'.$commento.'">'.$row['nome'].'</a></td>
				<td align="center" width="30%"><a href="'.$download.'">'.$download.'</a></td>
				<td align="center" width="10%">'.$row['dimensioni'].'</td>
				<td align="center" width="25%">'.$download_effettuati.'</td>
			  </tr>';
			
		}
		echo '</table>';
		$result->close();

	}
	
	function Elimina_file($key)
	{
		global $mysqli,$DBprefix,$AdminUsr;
		$account = new Account();
		$nick = $account->GetInfo(1);
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE delete_key LIKE '".$key."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$result->close();
		if($row['delete_key'] == $key)
		{
			if($row['public'] == 1 || $row['nickname'] == $nick || $nick == $AdminUsr)
			{
				$mysqli->query("DELETE FROM ".$DBprefix."host WHERE id LIKE '".$row['id']."'");
				if(file_exists($row['url'])) unlink($row['url']);
				
			}

		}else
		{
			echo "ID non valido";
		}
	}
}
$s = new Search();
$s->Verifico_Limite();
?>