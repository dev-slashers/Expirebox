<script type="text/javascript" src="src/jquery.js"></script>
<script type="text/javascript">
function Delete_Message(usr)
{
	$("div").remove("#Messaggio"+usr);
	$.get("?", { i: "msgdelete", id: usr } );//?i=msgdelete&id=
}
function textarea_max(id)
{
		
		if(document.getElementById(id).value.length >= 150)
		{
			document.getElementById(id).value = document.getElementById(id).value.substring(0,150);
		}
		
}
</script>

<?php
include 'manager.php';
class Message
{


	function giorno()
	{
		$d = date("l");
		$day = array(
			"Monday" => "Lunedi",
			"Tuesday" => "Martedi",
			"Wednesday" => "Mercoledi",
			"Thursday" => "Giovedi",
			"Friday" => "Venerdi",
			"Saturday" => "Sabato",
			"Sunday" => "Domenica",
		);
		foreach($day as $item => $key)
		{
			if(preg_match("/".$item."/i",$d))
			{
				return $key." alle ".date("G:i");
			}
		}
	}

	function Reset_messaggi()
	{
		global $mysqli,$DBprefix;
		$ac = new Account();
		$id_list = array();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."posta WHERE destinatario LIKE '".$ac->GetInfo(1)."'", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			$id_list[] = $row['id'];
		}
		$result->close();
		foreach($id_list as $item)
		{
			$mysqli->query("UPDATE ".$DBprefix."posta SET letto = '0' WHERE id=".$item);
		}
	}
	
	function Elimina_Messaggio($id)
	{
		global $mysqli,$DBprefix;
		$account = new Account();
		$nickname = $account->GetInfo(1);
		if($account->isLogin() == true)
		{
			
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."posta WHERE id LIKE '".$id."'", MYSQLI_USE_RESULT);
			while($row = $result->fetch_assoc())
			{
				$old_nick = $row['destinatario'];
				$mittente = $row['mittente'];
			}
			$result->close();
			if($nickname == $old_nick || $mittente == $nickname)
			{
				$mysqli->query("DELETE FROM ".$DBprefix."posta WHERE id LIKE '".$id."'");
				
			}
		}
	}
	function Write()
	{
		global $mysqli,$DBprefix;
		$AC = new Account();
		$msg = new Message();
		$rispondi = $_GET['reply'];
		if($AC->isLogin() == true)
		{
			if($rispondi != '' && $AC->Verifica_Nickname($rispondi) == false) $verifica = true;
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti", MYSQLI_USE_RESULT);
			echo '
				<form method="post" action="?i=write">
					<select name="destinatario">';
						while($row = $result->fetch_assoc()) 
						{
							if($row['password'] == $_COOKIE[$row['nickname']] || $row['activate'] == 0) continue;
							if($verifica == true)
							{
								echo '<option value="'.$rispondi.'">'.$rispondi.'</option>';
								break;
								
							}else
							{
								echo '<option value="'.$row['nickname'].'">'.$row['nickname'].'</option>';
							}
						}
						$result->close();
					echo'</select><br>
					<textarea id="txtesto" onchange="textarea_max(this.id)" onkeypress="textarea_max(this.id)" rows="10" cols="71" name="testo" placeholder="Testo"></textarea><br><br>
					<input type="submit" name="dati" value="Invia" class="Pulsante" />
				</form>';
			if(isset($_POST['dati']) && $_POST['testo'] != '')
			{
				$query = sprintf("INSERT INTO ".$DBprefix."posta (mittente, destinatario,testo,letto,ora) VALUES ('%s', '%s','%s','%s','%s')", $AC->GetInfo(1),$_POST['destinatario'],$_POST['testo'],1,$msg->giorno());
				$mysqli->query($query);
				echo "Messaggio Inviato";
			}
		}
	}
		
	function Read()
	{
		global $mysqli,$DBprefix;
		$msg = new Message();
		$account = new Account();
		$msg->Reset_messaggi();
		
		if($account->isLogin() == true)
		{
		
		//////////////Prendo l'avatar di tutti gli utenti////////////
		$avatarlist = array();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			$avatarlist[$row['nickname']] = $row['avatar'];
		}
		

		/////////////////////////////////////////////
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."posta WHERE destinatario LIKE '".$account->GetInfo(1)."' ORDER BY  `".$DBprefix."posta`.`id` DESC", MYSQLI_USE_RESULT);
		echo '<ul class="statuses">';
		while($row = $result->fetch_assoc()) 
		{
		$elimina_messaggio = "'".$row['id']."'";
					echo '<div id="corpo">
			<div id="Messaggio'.$row['id'].'">
			<li>
			<a href="#"><img class="avatar" src="'.$avatarlist[$row['mittente']].'" width="48" height="48" alt="avatar" /></a>
			<div class="campoTXT">
			<strong><a STYLE="float:left;" href="#">'.$row['mittente'].': </a></strong><div style="float:left;">'.htmlentities($row['testo']).'</div>
			<br><br><div class="date">'.$row['ora'].'</div><br><br>
			<a style="float:left;color:#0000FF" href="?i=write&reply='.$row['mittente'].'">Rispondi</a> <div style="float:left;"> - </div>
			<a style="float:left;color:#FF0000" href="javascript:Delete_Message('.$elimina_messaggio.')">Elimina</a>
			</div>
			<div class="clear"></div>
			</li></div></div>';
		}
		$result->close();
			echo '</ul></div>';
		}
	}
}
?>
