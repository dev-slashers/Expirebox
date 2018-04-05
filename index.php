<?php ob_start(); ?>
<html>
<link rel="stylesheet" type="text/css" href="src/menu.css" />
<script type="text/javascript" src="src/utily.js"></script>
<div id="loading">Carico . . .</div>
<body onLoad="Setting_c()"> </body>
<center>
<?php
include 'lib/administrator.php';
$account = new Account();
$file = new File();
$search = new Search();
$msg = new Message();
$profilo = new Profilo();
$admin0 = new Administrator();
$cmd = htmlentities($_GET['i']);
echo "<title>".$sitetitle."</title>".$account->Menu();


switch ($cmd)
{
	case "registra":
	$account->Registra();
	break;
	case "login":
	$account->Login();
	break;
	case "activate":
	$account->Activate($_GET['id']);
	break;
	case "logout":
	$account->LogoUt();
	break;
	case "home":
	$file->Upload();
	break;
	case "search":
	$search->Public_Download();
	break;
	case "delete":
	$search->Elimina_file($_GET['id']);
	echo "<br><br><br><br><br><br><img src='img/success-icon.png' /><br> File Eliminato <br><br> <input type='button' onclick='history.go(-1)' class='Pulsante' value='indietro' />";
	break;
	case "myfile":
	$search->Private_Download();
	break;
	case "read":
	$msg->Read();
	break;
	case "write":
	$msg->Write();
	break;
	case "msgdelete":
	$msg->Elimina_Messaggio($_GET['id']);
	echo "Messaggio Eliminato <br><br> <input class='Pulsante' type='button' onclick='history.go(-1)' value='Indietro' />";
	break;
	case "profile":
	$profilo->Profile($_GET['id']);
	break;
	case "admin_file":
	$admin0->File_Edit();
	break;
	case "admin_user":
	$admin0->User_Edit();
	break;
	case "recovery":
	$account->Recovery();
	break;
	default:
	$file->Upload();
	break;
}
?>
</center>
</html>
<?php ob_end_flush();?>