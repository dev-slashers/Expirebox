
<style type="text/css">
a:link {color: #000000; text-decoration: underline; }
a:active {color: #0000ff; text-decoration: underline; }
a:visited {color: #0066CC; text-decoration: underline; }
a:hover {color: #ff0000; text-decoration: none; }
</style> 

<?php
include 'message.php';
class Profilo
{
	function isPublic($nick)
	{
		global $DBprefix,$mysqli;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$nick."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$result->close();
		if($row['public_page'] == 1) return true;
				
	}
	function Profile($profilo)
	{
		global $DBprefix,$mysqli,$sitehost;
		$account = new Account();
		$profilo_cls = new Profilo();
		if($profilo_cls->isPublic($profilo) == true && $account->Verifica_Nickname($profilo) == false && $profilo != '') 
		{	
				echo '<form method="POST" action="?i=profile&id='.$profilo.'">
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
				if($_POST['key_search'])
				{
					$result = $mysqli->query("SELECT * FROM ".$DBprefix."host WHERE nome LIKE '%".$_POST['key_search']."%'", MYSQLI_USE_RESULT);
				}else
				{
					$result = $mysqli->query("SELECT * FROM ".$DBprefix."host ORDER BY  `".$DBprefix."host`.`id` DESC ", MYSQLI_USE_RESULT);
				}
				while($row = $result->fetch_assoc())
				{
					if($row['nickname'] != $profilo) continue;
					if($row['public'] == 0) continue;
					$download = $sitehost.'download.php?id='.$row['code'];
					$download_effettuati = $row['limite'] == 'MAX' ? "<img src='img/infinity.png' heigh='20' width='20' />":($row['limite'] - $row['numero_download']);
					$commento = $row['commento'] != '' ? "alert('".$row['commento']."')" : "alert('Nessun Commento')";
						echo '
						<tr>
						<td align="center" width="25%"><a href="javascript:'.$commento.'">'.$row['nome'].'</a></td>
						<td align="center" width="30%"><a href="'.$download.'">'.$download.'</a></td>
						<td align="center" width="10%">'.$row['dimensioni'].'</td>
						<td align="center" width="25%">'.$download_effettuati.'</td>
					  </tr>';
				}
				$result->close();
				echo '</table>';
		}
	}
}

$s = new Search();
$s->Verifico_Limite();
?>
