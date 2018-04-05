<?php ob_start(); ?>
<script type="text/javascript" src="src/jquery.js"></script>
<script type="text/javascript">
var stile = "top=200, left=800, width=400, height=640, status=no, menubar=no, toolbar=no scrollbars=no";
function Popup(apri) 
{
  window.open(apri, "", stile);
}

function captcha_recovery()
{
	var c = MD5(document.getElementById("secret_key").value);

	if(document.getElementById("ckey").value == c.substring(0,5))
	{
		document.getElementById("OK_captcha").style.display = "inline";
		document.getElementById("ERRORE_captcha").style.display = "none";
	}else
	{
		document.getElementById("OK_captcha").style.display = "none";
		document.getElementById("ERRORE_captcha").style.display = "inline";
	}
	
}

function Login_Password(pwd)
{
	if(pwd.length >= 3)
	{
		$("#password").html("<img src='img/action_ok.png' />");
	}else
	{
		$("#password").html("<img src='img/action_no.png' />");
	}
	
}
function Login_Nome(Nome)
{
	if(Nome.length >= 2)
	{
		$("#nome").html("<img src='img/action_ok.png' />");
	}else
	{
		$("#nome").html("<img src='img/action_no.png' />");
	}
}
function Login_Nickname(nick)
{
	$.post("lib/account.php", {nickname : nick}, function(data){$("#nickname").html(data)});
}
function Login_Mail(mail)
{
	$.post("lib/account.php", {email : mail}, function(data){$("#email").html(data)});
}
</script>
<?php
include 'config.php';
class Account
{
	function StrRandom()
	{
		$abc= array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"); 
		$num= array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
		$y = "";
		for($i = 0;$i <=7;$i++)
		{
			$y .= $abc[rand(0,25)].$num[rand(0,9)];
		}
		return $y;
	}
	function Verifica_Nickname($nickname)
	{
		global $mysqli,$DBprefix;
		$verifica = false;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$nickname."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$username = $row['nickname'];
		$result->close();
		if($username != $nickname) $verifica = true;
		return $verifica;
	}
	
	function Verifica_Email($email)
	{
		global $mysqli,$DBprefix;
		$verifica = false;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE email LIKE '".$email."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		if($email != $row['email']) $verifica = true;
		return $verifica;
	}
	function isLogin() //verifico se l'utente è connesso
	{
		global $mysqli,$DBprefix;
		$verifica = false;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			if($_COOKIE[$row['nickname']] == $row['password'] && $row['activate'] == 1) $verifica = true;
		}
		$result->close();
		return $verifica;
	}
	
	
	function Verifica_Dati($nick,$password)
	{
		global $mysqli,$DBprefix;
		$verifica = false;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE nickname LIKE '".$nick."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		if($nick == $row['nickname'] && md5($password) == $row['password'] && $row['activate'] == 1) $verifica = true;
		return $verifica;
	}
	
	
	function GetInfo($n)// ritorna il nickname(1) o l'id(2)
	{
		global $mysqli,$DBprefix;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			if($_COOKIE[$row['nickname']] == $row['password'] && $row['activate'] == 1) 
			{
				$nickname = $row['nickname'];
				$id = $row['id'];
				$avatar = $row['avatar'];
			}
		}
		$result->close();
		switch ($n)
		{
			case 1:
			return $nickname;
			break;
			case 2:
			return $id;
			break;
			case 3:
			return $avatar;
			break;
			default:
			return "";
			break;
		}
	}
	function Controllo_Caratteri($nick)
	{
		$caratteri = array(" ","<b>","<",">","'",'"',"<li>","*","+","&","%","$","!","@","#","è","{","}","[","]","(",")","=","^","?","°","§");
		$verifica = true;
		foreach($caratteri as $item)
		{
			if(strpos($nick,$item) != 0) $verifica = false;
		}
		return $verifica;
	}
	function LogoUt()
	{
		$account = new Account();
		if($account->isLogin() == true) setcookie($account->GetInfo(1),NULL,-1);
		echo '<meta HTTP-EQUIV="REFRESH" content="0; url=?i=home">';
	}
	function change_password($email,$new_password)
	{
		global $mysqli,$DBprefix;
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE email LIKE '".$email."'", MYSQLI_USE_RESULT);
		$row = $result->fetch_assoc();
		$result->close();
		$mysqli->query("UPDATE ".$DBprefix."utenti SET password = '".md5($new_password)."' WHERE id=".$row['id']);
	}
	function Recovery()
	{
		global $sitehost,$AdminMail,$sitetitle;
		$account = new Account();
		$password = $account->StrRandom();
		$new_password = substr($account->StrRandom(),0,6);
		if(isset($_POST['dati'])) 
		{	
			
			if($_POST['mail'] != '' && $account->Verifica_Email($_POST['mail']) == false && $_POST['captcha'] == substr(md5($_POST['secret_key']),0,5))
			{
				$account->change_password($_POST['mail'],$new_password);
				echo "Per ragioni di sicurezza la password è stata cambiata, reiceverai la nuova password per posta,se non ricevi nulla controlla in posta indesiderata";
				
				$testo = "Abbiamo ricevuto una richiesta di recupero password, per ragioni di sicurezza la password e' stata resettata.
				La nuova password e' ".$new_password;
				
					$intestazioni= "From:".$AdminMail;
					mail($_POST['mail'], "Recupero password ".$sitetitle, $testo, $intestazioni);
					
			}else
			{
				$ErrorList = "";
				if($_POST['mail'] != '' && $account->Verifica_Email($_POST['mail']) == true) $ErrorList .= "<li>Account email non presente nel database<br>";
				if($_POST['mail'] == '') $ErrorList .= "<li>Campo vuoto<br>";
				if($_POST['captcha'] != substr(md5($_POST['secret_key']),0,5)) $ErrorList .= "<li>Codice Errato<br>";
				echo $ErrorList."<br><br><input type='button' value='Indietro' class='Pulsante' onclick='javascript:history.go(-1)' />";
			}
		}else
		{
			echo '<form method="POST" action="?i=recovery">
			<input type="text" name="mail" placeholder="E-Mail" size="22" maxlength="60" /><br>
			<img src="lib/captcha.php?cod='.$password.'" /> <input type="hidden" id="secret_key" value="'.$password.'" name="secret_key">
			<br> <input type="text" id="ckey" onkeyup="captcha_recovery()" name="captcha" maxlength="5" placeholder="Codice" size="10" /> <img id="OK_captcha" src="img/action_ok.png" style="display: none;" /> <img id="ERRORE_captcha" src="img/action_no.png"  style="display: none;" />
			<br>
			<input type="submit" name="dati" class="Pulsante" value="Recupera" />
			</form>
			';
		}
	}
	function Activate($key)
	{
		global $mysqli,$DBprefix;
		$account = new Account();
		if($account->isLogin() == false)
		{
			$result = $mysqli->query("SELECT * FROM ".$DBprefix."utenti WHERE keyID LIKE '".$key."'", MYSQLI_USE_RESULT);
			$row = $result->fetch_assoc();
			$result->close();
			if($key == $row['keyID'] && $row['activate'] == 0 && $key != '') 
			{
				$mysqli->query("UPDATE ".$DBprefix."utenti SET activate = '1' WHERE id=".$row['id']);
				setcookie($row['nickname'],$row['password']);
				echo "Account Attivato";
				echo '<meta HTTP-EQUIV="REFRESH" content="2; url=?i=home">';
			}
		}else
		{
			echo "Effettua il <a href='?i=logout'>logout</a> prima di attivate l'account";
		}
	}
	function Login()
	{
		global $mysqli,$DBprefix;
		$account = new Account();
		if($account->isLogin() == true)
		{
			echo "Già sei connesso";
		}else
		{
			if(isset($_POST['dat']))
			{
						if($account->Verifica_Dati($_POST['nick'],$_POST['password']) == true) 
						{
							setcookie($_POST['nick'],md5($_POST['password']));
							echo "Login effettuato";
							echo '<meta HTTP-EQUIV="REFRESH" content="1; url=?i=home">';
						}else
						{
							echo "<li>Dati Errati<br>
							<br> <input type='button' class='Pulsante' value='indietro' onclick='history.go(-1)' />";
						}
			}else
			{
				echo '<form method="post" action="?i=login">
				<input type="text" name="nick" placeholder="Nickname" size="22" maxlength="22" /><br>
				<input type="password" name="password" placeholder="Password" size="22" maxlength="22" /><br><br>
				<a href="?i=recovery">Password Dimenticata ?</a><br><br>
				<input type="submit" class="Pulsante" name="dat" value="Accedi" />
				</form>
				';
			}
		}
	}
	function Registra()
	{
		global $mysqli,$DBprefix,$sitehost,$AdminMail,$sitetitle;
		$cls = new Account();
		$secret_key = substr($cls->StrRandom(),0,5);
		$activate_key = strtoupper($cls->StrRandom());
		if($cls->isLogin() == true)
		{
			echo "Già sei registrato";
		}else
		{
			if(isset($_POST['dati']))
			{
				if(strlen($_POST['nickname']) >= 3 && $cls->Controllo_Caratteri($_POST['nickname']) == true && $cls->Verifica_Nickname($_POST['nickname']) == true && $cls->Verifica_Email($_POST['mail']) == true && strlen($_POST['nome']) >= 2 && strlen($_POST['password']) >= 3 && strpos($_POST['mail'],"@") != 0 && strpos($_POST['mail'],".") != 0 && $_POST['captcha_text'] == substr(md5($_POST['captcha_key']),0,5))
				{
					$query = sprintf("INSERT INTO ".$DBprefix."utenti (nickname, password, keyID,email,activate,avatar,public_page) VALUES ('%s', '%s','%s', '%s','%s', '%s','%s')", $_POST['nickname'],md5($_POST['password']),$activate_key,$_POST['mail'],0,"img/default_avatar.jpg",0);
					$mysqli->query($query);
					$testo = "Caro ".$_POST['nome']." grazie per esserti iscritto, per iniziare ad utilizzare il tuo account conferma la registrazione ".$sitehost."?i=activate&id=".$activate_key;
					$intestazioni= "From:".$AdminMail;
					mail($_POST['mail'], "Registrazione ".$sitetitle, $testo, $intestazioni);
					echo "Ti e' stata inviata un'email di conferma,se non ricevi nulla controlla in posta indesiderata.";
		
				}else
				{
					$ErrorList = "";
					if($cls->Verifica_Nickname($_POST['nickname']) == false && $_POST['nickname'] != '') $ErrorList .= "<li> Nickname non disponibile <br>";
					if($cls->Verifica_Email($_POST['mail']) == false && $_POST['mail'] != '') $ErrorList .= "<li>Email non disponibile <br>";
					if(strlen($_POST['nome']) < 2) $ErrorList .= "<li> Nome Breve <br>";
					if($cls->Controllo_Caratteri($_POST['nickname']) == false && $_POST['nickname'] != '') $ErrorList .= "<li> Carattere nickname non accettato <br>";
					if(strlen($_POST['nickname']) < 3) $ErrorList .= "<li> Nickname Breve <br>";
					if(strlen($_POST['password']) < 3) $ErrorList .= "<li> Password Breve <br>";
					if(strpos($_POST['mail'],"@") == 0 || strpos($_POST['mail'],".") == 0) $ErrorList .= "<li> Formato email errato <br>";
					if($_POST['captcha_text'] != substr(md5($_POST['captcha_key']),0,5)) $ErrorList .= "<li> Codice captcha errato <br>";
					echo $ErrorList."<br><br> <input type='button' class='Pulsante' value='Indietro' onclick='history.go(-1)' />";
				}
				
			}else
			{
				echo '<form method="post" action="?i=registra">
				<input type="text" name="nome"  placeholder="Nome" size="22" onkeyup="Login_Nome(this.value)" maxlength="22" /><div style="display: inline;" id="nome"></div><br>
				<input type="text" name="nickname" placeholder="Nickname" size="22" onchange="Login_Nickname(this.value)" maxlength="22" /><div id="nickname" style="display: inline;"></div><br>
				<input type="password" name="password" placeholder="Password" size="22" onkeyup="Login_Password(this.value)" maxlength="22" /><div id="password" style="display: inline;"></div><br>
				<input type="text" name="mail" placeholder="E-Mail" size="22" maxlength="60" onchange="Login_Mail(this.value)" /><div id="email" style="display: inline;"></div><br>
				<img src="lib/captcha.php?cod='.$secret_key.'" /><input type="hidden" id="secret_key" value='.$secret_key.' name="captcha_key" />
				<br>
				<input type="text" id="ckey" onkeyup="captcha()" name="captcha_text" size="10" placeholder="Captcha" maxlength="5" /><img id="OK_captcha" src="img/action_ok.png" style="display: none;" /> <img id="ERRORE_captcha" src="img/action_no.png"  style="display: none;" />
				<br><br>
				<input type="submit" class="Pulsante" name="dati" value="Registra" />
				</form>';
			}
		}
	}
	
	
	
	
	
	function controllo_posta()
	{
		global $mysqli,$DBprefix;
		$ac = new Account();
		$n = 0;
		$nickname = $ac->GetInfo(1);
		$result = $mysqli->query("SELECT * FROM ".$DBprefix."posta WHERE destinatario LIKE '".$nickname."'", MYSQLI_USE_RESULT);
		while($row = $result->fetch_assoc())
		{
			$n += $row['letto'];
		}
		$result->close();
		if($n > 0)
		{
			return " (".$n.")";
		}else
		{
			return "";
		}
	}
	
function Login_Nickname($nick)
{
		$account = new Account();
		if($account->Verifica_Nickname($nick) == true && $account->Controllo_Caratteri($nick) == true && strlen($nick) >= 3)
		{
			return "<img src='img/action_ok.png'/>";
		}else
		{
			return "<img src='img/action_no.png' />";
		}
}

function Login_Email($mail)
{
	$account = new Account();
	if($account->Verifica_Email($mail) == true && strpos($mail,"@") != 0 && strpos($mail,".") != 0)
	{
		return "<img src='img/action_ok.png' />";
	}else
	{
		return "<img src='img/action_no.png' />";
	}
}
	
	function Menu()
	{
		global $AdminUsr;
		$account = new Account();
		$virgoletta = "'";
		if($account->isLogin() == true && $account->GetInfo(1) == $AdminUsr) //menu amministratore
		{
			return '<ul id="mymenu">
			<li><a href="#"><strong>'.$account->GetInfo(1).' <img src="img/user.png" heigh="20" width="20" /></strong></a><ul>
		   <li><a href="javascript:Popup('.$virgoletta.'setting.php?action=mysetting'.$virgoletta.')">Gestione Account</a></li>
		   <li><a href="?i=admin_user">Gestione Utenti(Admin)</a></li>
		   <li><a href="?i=admin_file">Gestisci File(Admin)</a></li>
		   <li><a href="?i=myfile">I Miei File</a></li>
		   <li><a href="?i=logout">Esci</a></li>
		   </ul>
		   	<li><a href="#"><strong>Posta <img src="img/posta.png" heigh="20" width="20" /></strong></a><ul>
			<li><a href="?i=write">Scrivi</a></li> 
		   <li><a href="?i=read">Ricevuti'.$account->controllo_posta().'</a></li> 
		   </ul>
		   <li><a href="?i=search"><strong>Cerca <img src="img/find.png" heigh="20" width="20" /></strong></a></li>
			<li><a href="?i=home"><strong>Home <img src="img/home.png" heigh="20" width="20" /></strong></a></li>
		</ul><br><br>';
		
		}elseif($account->isLogin() == true) //menu utente registrato
		{
			return '<ul id="mymenu">
			<li><a href="#"><strong>'.$account->GetInfo(1).' <img src="img/user.png" heigh="20" width="20" /></strong></a><ul>
		   <li><a href="javascript:Popup('.$virgoletta.'setting.php?action=mysetting'.$virgoletta.')">Gestione Account</a></li>
		   <li><a href="?i=myfile">I Miei File</a></li>
		   <li><a href="?i=logout">Esci</a></li>
		   </ul>
		   	<li><a href="#"><strong>Posta <img src="img/posta.png" heigh="20" width="20" /></strong></a><ul>
			<li><a href="?i=write">Scrivi</a></li> 
		   <li><a href="?i=read">Ricevuti'.$account->controllo_posta().'</a></li> 
		   </ul>
		   <li><a href="?i=search"><strong>Cerca <img src="img/find.png" heigh="20" width="20" /></strong></a></li>
			<li><a href="?i=home"><strong>Home <img src="img/home.png" heigh="20" width="20" /></strong></a></li>
		</ul><br><br>';
		
		}else //menu ospite
		{
			return '<ul id="mymenu">
			<li><a href="#"><strong>Tu <img src="img/user.png" heigh="20" width="20" /></strong></a><ul>
		   <li><a href="?i=registra">Registra</a></li>
		   <li><a href="?i=login">Login</a></li>
		   </ul>
		   <li><a href="?i=search"><strong>Cerca <img src="img/find.png" heigh="20" width="20" /></strong></a></li>
			<li><a href="?i=home"><strong>Home <img src="img/home.png" heigh="20" width="20" /></strong></a></li>
		</ul><br><br>';
		}
	}
}
$account = new Account();
if(!isset($_POST['dati']))
{
	if($_POST['nickname'] != '') echo $account->Login_Nickname($_POST['nickname']);
	if($_POST['email'] != '') echo $account->Login_Email($_POST['email']);
}
?>


<?php
    ob_end_flush();
?>