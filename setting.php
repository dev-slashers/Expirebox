<?php ob_start(); ?>
<html>
<link rel="stylesheet" type="text/css" href="src/menu.css" />
<script type="text/javascript" src="src/utily.js"></script>
<script type="text/javascript">
function Controllo_PWD()
{
	if(document.getElementById("old_password").value.length >= 3 && document.getElementById("new_password").value.length)
	{
		document.getElementById("passwordp").style.visibility = "visible";
	}else
	{
		document.getElementById("passwordp").style.visibility = "hidden";
	}
}
function checkb()
{
	document.getElementById("pagina").style.visibility = "visible";
}

function delete_()
{
	if(document.getElementById("password_delete").value.length >= 3)
	{
		document.getElementById("d_account").style.visibility = "visible";
	}else
	{
		document.getElementById("d_account").style.visibility = "hidden";
	}
}

function captcha()
{
	var c = MD5(document.getElementById("secret_key").value);

	if(document.getElementById("captcha_key").value == c.substring(0,5))
	{
		document.getElementById("OK_captcha").style.display = "inline";
		document.getElementById("ERRORE_captcha").style.display = "none";
	}else
	{
		document.getElementById("OK_captcha").style.display = "none";
		document.getElementById("ERRORE_captcha").style.display = "inline";
	}
	delete_();
}
function showavatar()
{
	document.getElementById("avatar").style.visibility = "visible";
}

</script>
<center>
<?php
include 'lib/message.php';
echo '<title>'.$sitetitle.'</title>';
class Setting
{

	function Avatar()
	{
		define(UPLOAD_DIR,"./img/avatar/");
		global $mysqli,$DBprefix;
		$account = new Account();
			$estensione = explode("/",$_FILES['avatar']['type']);
			$filename = UPLOAD_DIR.$account->StrRandom().$_FILES['avatar']['name'];
			if($estensione[1] == "jpeg" || $estensione[1] == "png" || $estensione[1] == "gif")
			{
				move_uploaded_file($_FILES['avatar']['tmp_name'],$filename);
				
				$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$account->GetInfo(1)."'", MYSQLI_USE_RESULT);
				while($row = $result->fetch_assoc())
				{
					$id = $row['id'];
					$old_avatar = $row['avatar'];
				}
				$result->close();
				$mysqli->query("UPDATE ".$DBprefix."utenti SET avatar = '".substr($filename,2,strlen($filename) -2)."' WHERE id=".$id);
				if($old_avatar != 'img/default_avatar.jpg' && file_exists($old_avatar)) unlink($old_avatar);
				echo "<script>alert('Avatar cambiato')</script>";
			}else
			{
					echo "<script>alert('Estensione non consentita')</script>";
			}
	}
	
	function Change_Password($old_password,$new_password)
	{
		global $mysqli,$DBprefix;
		$account = new Account();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$account->GetInfo(1)."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$result->close();
		if($row['password'] == md5($old_password))
		{
			$mysqli->query("UPDATE ".$DBprefix."utenti SET password = '".md5($new_password)."' WHERE id=".$row['id']);
			setcookie($row['nickname'],md5($new_password));
			return true;
		}
	}
	function Profile_is_Public()
	{
		global $mysqli,$DBprefix;
		$account = new Account();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$account->GetInfo(1)."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$result->close();
		if($row['public_page'] == true) return true;
	}
	

	
	function Delete_profile()
	{
		global $mysqli,$DBprefix;
		$se = new Search();
		$file_list = array();
		$message_list = array();
		$account = new Account();
		$msg = new Message();
		$nickname = $account->GetInfo(1);
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
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$account->GetInfo(1)."'", MYSQLI_USE_RESULT);
			$row = $result->fetch_assoc();
			if($row['avatar'] != 'img/default_avatar.jpg' && file_exists($row['avatar'])) unlink($row['avatar']);
			$result->close();
		foreach($file_list as $file)
		{
			$se->Elimina_file($file);
		}
		foreach($message_list as $mlist)
		{
			$msg->Elimina_Messaggio($mlist);
		}
		$mysqli->query("DELETE FROM ".$DBprefix."utenti WHERE nickname LIKE '".$account->GetInfo(1)."'");
	}
	function Verifica_Password($pwd)
	{
			global $mysqli,$DBprefix;
		$account = new Account();
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$account->GetInfo(1)."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$result->close();
		if(md5($pwd) == $row['password']) return true;
	}
	function MySetting()
	{
		global $mysqli,$DBprefix,$sitehost;
		$set = new Setting();
		$account = new Account();
		if(isset($_POST['password']))
		{
			
			if($set->Change_Password($_POST['old_password'],$_POST['new_password']) == true) 
			{	
				echo "<script>alert('Password Cambiata');</script>";
			}
		}
		if(isset($_POST['pagina']))
		{
			if($_POST['public_p'] == 'on')
			{
				$mysqli->query("UPDATE ".$DBprefix."utenti SET public_page = '1' WHERE id=".$account->GetInfo(2));
				$profile = $sitehost."profile.php?id=".$account->GetInfo(1);
				echo "<script>alert('Profilo Pubblico');</script>";
			}else
			{
				$mysqli->query("UPDATE ".$DBprefix."utenti SET public_page = '0' WHERE id=".$account->GetInfo(2));
				echo "<script>alert('Il profilo non è più visibile');</script>";
			}
		}
		if(isset($_POST['avatar']))
		{
			$set->Avatar();
		}
		if(isset($_POST['delete_account']))
		{
			if($_POST['captcha_key'] == substr(md5($_POST['secret_key']),0,5) && $set->Verifica_Password($_POST['password_delete']) == true) 
			{
				$set->Delete_profile();
				echo '<script>window.onload=function(){alert("Account Eliminato !");self.close();}</script>';
			}
		}
		$account = new Account();
		$password = $account->StrRandom();
		$profile = $sitehost."?i=profile&id=".$account->GetInfo(1);
		$profile_public = $set->Profile_is_Public() == true ? 'checked="checked"' : '';
		$profile_site = $set->Profile_is_Public() == true ? "<a href='".$profile."' target='_blank'><b>Pagina Profilo</b></a>" : "";
		echo '
		<form method="POST" action="setting.php?action=mysetting" enctype="multipart/form-data">
		<fieldset class="groupbox">
		<legend>Cambio Password</legend>
		<br>
		<table>
		<tr>
		<td><input type="password" onchange="Controllo_PWD()" onkeypress="Controllo_PWD()" id="old_password" name="old_password" placeholder="Vecchia Password" size="22" maxlength="22" /> </td>
		</tr>
		<tr>
		<td><input type="password" onchange="Controllo_PWD()" onkeypress="Controllo_PWD()" id="new_password" name="new_password" placeholder="Nuova Password" size="22" maxlength="22" /> </td>
		</tr>
		<tr>
		<td><input style="visibility: hidden;" type="submit" id="passwordp" name="password" class="Pulsante" value="Conferma" /> </td>
		</tr>
		</table>
		</fieldset>
		
		<fieldset class="groupbox">
		<legend>Elimina Account</legend>
		<br>
		<table>
		<tr>
		<td><input type="password" id="password_delete" onchange="delete_()" onkeypress="delete_()" name="password_delete" placeholder="Password" size="22" maxlength="22" /> </td>
		</tr>
		<tr>
		<td><img src="lib/captcha.php?cod='.$password.'" /></td>
		</tr>
		<tr>
		<td><input type="text" id="captcha_key" name="captcha_key" onkeyup="captcha()" placeholder="Codice" size="10" maxlength="5" /><input type="hidden" value="'.$password.'" id="secret_key" name="secret_key" /><img id="OK_captcha" src="img/action_ok.png" style="display: none;" /> <img id="ERRORE_captcha" src="img/action_no.png"  style="display: none;" /> </td>
		</tr>
		
		<tr>
		<td><input style="visibility: hidden;" class="Pulsante" type="submit" id="d_account" name="delete_account" value="Conferma" /></td>
		</tr>
		</table>
		</fieldset>
		
		
		

		<fieldset class="groupbox">
		<legend>Avatar</legend>
		<br>
		<table>
		<tr>
			<td><input type="file" name="avatar" onclick="showavatar()" /></td>
		</tr>
		<tr>
			<td><input type="submit" name="avatar" id="avatar" style="visibility: hidden;" class="Pulsante" value="Cambia" /></td>
		</tr>
		</table>
		</fieldset>

		
		
		
		<fieldset class="groupbox">
		<legend>Informazioni</legend>
		<br>
		<table>
		<tr>
		<td>Pagina Pubblica: </td>
		<td><input onclick="checkb()" type="checkbox" '.$profile_public.' name="public_p" /></td>
		</tr>
		<tr>
		<td>'.$profile_site.'</td>
		</tr>
		<tr>
		<td><input style="visibility: hidden;" id="pagina" class="Pulsante" type="submit" name="pagina" value="Conferma" /></td>
		</tr>
		</table>
		</fieldset>
		
		
		</form>
		';
	}

}

$s = new Setting();
$account = new Account();
$action = htmlentities($_GET['action']);
if($account->isLogin() == true)
{
	switch($action)
	{
		case "mysetting":
		$s->Mysetting();
		break;
		default:
		echo "Comando non disponibile";
		break;
	}
}
?>
</center>
</html>
<?php ob_end_flush();?>