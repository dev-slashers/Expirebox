<?php
$sitetitle = "ExpireBox";
$sitehost = "http://192.168.0.3/ExpireBox";
$dirupload = "up/"; //cartella dove caricare i file
/////////////////////Database///////////////
$DBhost = "localhost";
$DBuser = "root";
$DBpassword = "";
$DBname = "expirebox";
$DBprefix = "exp_"; //facoltativo
$install = true;
///////////////////////////////////////////

/////////////////Dati Amministratore///////
$AdminUsr = "Administrator";
$AdminMail = "admin@email.it";
//////////////////////////////////////////
$estensione_file = array("zip","jpg","pdf","png","exe","rar","iso","mp3"); //estensioni consentite
$limite_download = 100;

$mysqli = new mysqli($DBhost,$DBuser,$DBpassword,$DBname); 


if($install)
{
	$mysqli->autocommit(true);
	$mysqli->query("
	CREATE TABLE ".$DBprefix."host (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	".$DBprefix."url TEXT NOT NULL,
	".$DBprefix."code TEXT NOT NULL,
	".$DBprefix."limite TEXT NOT NULL,
	".$DBprefix."numero_download TEXT NOT NULL,
	".$DBprefix."dimensioni TEXT NOT NULL,
	".$DBprefix."nome TEXT NOT NULL,
	".$DBprefix."commento TEXT NOT NULL,
	".$DBprefix."delete_key TEXT NOT NULL,
	".$DBprefix."nickname TEXT NOT NULL,
	".$DBprefix."public TEXT NOT NULL,
	PRIMARY KEY(id)
	);");

	$mysqli->query("
	CREATE TABLE ".$DBprefix."utenti (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	".$DBprefix."nickname TEXT NOT NULL,
	".$DBprefix."password TEXT NOT NULL,
	".$DBprefix."keyID TEXT NOT NULL,
	".$DBprefix."email TEXT NOT NULL,
	".$DBprefix."activate TEXT NOT NULL,
	".$DBprefix."avatar TEXT NOT NULL,
	".$DBprefix."public_page TEXT NOT NULL,
	PRIMARY KEY(id)
	);
	");

	$mysqli->query("
	CREATE TABLE ".$DBprefix."posta (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	".$DBprefix."mittente TEXT NOT NULL,
	".$DBprefix."destinatario TEXT NOT NULL,
	".$DBprefix."testo TEXT NOT NULL,
	".$DBprefix."letto TEXT NOT NULL,
	".$DBprefix."ora TEXT NOT NULL,
	PRIMARY KEY(id)
	);
	");
}
?>
