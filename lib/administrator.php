<script type="text/javascript">
function Delete(nick)
{
	if(confirm("l'utente sara' eliminato e con essi tutti i suoi file proseguire??"))
	{
		window.location.href="?i=admin_user&delete=" + nick;
	}
}
</script>
<?php
include 'profile.php';
$s = new Search();
$s->Verifico_Limite();
class Administrator
{

		function Delete_profile($nickname)
		{
			global $mysqli,$DBprefix;
			$se = new Search();
			$file_list = array();
			$message_list = array();
			$account = new Account();
			$msg = new Message();
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."host", MYSQLI_USE_RESULT);
			while($row = $result->fetch_assoc())
			{
				if($row['nickname'] != $nickname)continue;
				$file_list[] = $row['delete_key'];
			}
			$result->close();
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."posta", MYSQLI_USE_RESULT);
			while($row = $result->fetch_assoc())
			{
				if($row['mittente'] == $nickname || $row['destinatario'] == $nickname) $message_list[] = $row['id'];
			}
			$result->close();
			
			//////////////
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$nickname."'", MYSQLI_USE_RESULT);
			$row = $result->fetch_assoc();
			if($row['avatar'] != 'img/default_avatar.jpg' && file_exists($row['avatar'])) unlink($row['avatar']);
			$result->close();
			/////////////
			
			foreach($file_list as $file)
			{
				$se->Elimina_file($file);
			}
			foreach($message_list as $mlist)
			{
				$msg->Elimina_Messaggio($mlist);
			}
			$mysqli->query("DELETE FROM ".$DBprefix."utenti WHERE nickname LIKE '".$nickname."'");
		}
	
	function User_Edit()
	{
		global $AdminUsr,$mysqli,$DBprefix,$sitehost;
		$account = new Account();
		$admin = new Administrator();
		if($account->isLogin() == true && $account->GetInfo(1) == $AdminUsr)
		{
			if($_GET['delete'] != '' && $account->Verifica_Nickname($_GET['delete']) == false)/////// elimino account
			{
				$admin->Delete_profile($_GET['delete']);
				echo "<script>alert('Account Eliminato')</script>"; 
			}
		/////////////////////
		$numero_file = array();
		$nome_file = array();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."host", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			$numero_file[$row['nickname']] += 1;
			$nome_file[$row['nickname']] .= $row['nome']."\\n";
		}
		$result->close();

		////////////////////
			echo '
		 <table class="tabella" border="1" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
			<td width="15%" height="19">
			<p class="tabella_caratteri"><b>Nickname</b></td>
			<td width="30%" height="19">
			<p class="tabella_caratteri"><b>Email</b></td>
			<td width="17%" height="19">
			<p class="tabella_caratteri"><b>File Caricati</b></td>
			<td width="12%" height="19">
			<p class="tabella_caratteri"><b>Profilo Pubblico</b></td>
			<td width="15%" height="19" class="tabella_caratteri">
			<b>Account Attivo</b></td>
			<td width="50%" height="19">
			<p class="tabella_caratteri"><b>Elimina</b></td>
		  </tr>
			';
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti ORDER BY  `".$DBprefix."utenti`.`id` DESC ", MYSQLI_USE_RESULT);
			while($row = $result->fetch_assoc())
			{
				if($row['nickname'] == $AdminUsr) continue;
				$messaggio = "javascript:alert('".$nome_file[$row['nickname']]."')";
				//$is_activate = $row['activate'] == 1 ? "'Account Attivo'":"'Account non ancora attivato'"; 			Stringa:	<td align="center" width="15%"><a href="javascript:alert('.$is_activate.')">'.$row['nickname'].'</a></td>
				$n_file = $numero_file[$row['nickname']] != '' ? '<a href="'.$messaggio.'">'.$numero_file[$row['nickname']].'</a>':'0';
				$pagina_pubblica = $row['public_page'] == 1 ? '<img src="img/action_ok.png" />':'<img src="img/action_no.png" />';
				$account_activate = $row['activate'] == 1 ? '<img src="img/action_ok.png" />':'<img src="img/action_no.png" />';
				$nick_delete = "'".$row['nickname']."'";
				echo '
				<tr>
				<td align="center" width="15%"><a href="#">'.$row['nickname'].'</a></td>
				<td align="center" width="30%"><a href="mailto:'.$row['email'].'">'.$row['email'].'</a></td>
				<td align="center" width="17%">'.$n_file.'</td>
				<td align="center" width="12%">'.$pagina_pubblica.'</td>
				<td align="center" width="15%">'.$account_activate.'</td>
				<td align="center" width="50%"><a href="javascript:Delete('.$nick_delete.')"><img src="img/delete.png" /></a></td>
			
			  </tr>
				';
			}
			$result->close();
			echo '
			</table>
			';
		}
	}
	function File_Edit()
	{
		global $AdminUsr,$mysqli,$DBprefix,$sitehost;
		$account = new Account();
		if($account->isLogin() == true && $account->GetInfo(1) == $AdminUsr)
		{
			echo '
		<table class="tabella" border="1" cellpadding="0" cellspacing="0" width="100%">
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
		  </tr>
			';
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."host ORDER BY  `".$DBprefix."host`.`id` DESC ", MYSQLI_USE_RESULT);
			while($row = $result->fetch_assoc())
			{
				$download = $sitehost.'download.php?id='.$row['code'];
				$download_effettuati = $row['limite'] == 'MAX' ? "<img src='img/infinity.png' heigh='20' width='20' />":($row['limite'] - $row['numero_download']);
				$commento = $row['commento'] != '' ? "alert('".$row['commento']."')" : "alert('Nessun Commento')";
				$delete_ = "<a href='?i=delete&id=".$row['delete_key']."'><img src='img/delete.png' /></a>";
				echo '<tr>
				<td align="center" width="31%"><a href="javascript:'.$commento.'">'.$row['nome'].'</a></td>
				<td align="center" width="30%"><a href="'.$download.'">'.$download.'</a></td>
				<td align="center" width="18%">'.$row['dimensioni'].'</td>
				<td align="center" width="8%">'.$delete_.'</td>
				<td align="center" width="38%">'.$download_effettuati.'</td>
			  </tr>';
			}
			$result->close();
			echo '</table>';
		}else
		{
			echo "Non hai i permessi per accedere a questa pagina";
		}
	}
}
?>
