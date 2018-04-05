<?php
header("Content-type: image/png");
$x = 200;
$y = 70;
$code = substr(md5($_GET['cod']),0,5);
$space = $x / (strlen($code)+1);
$img = imagecreatetruecolor($x,$y);
$bg = imagecolorallocate($img,255,255,255);
$border = imagecolorallocate($img,0,0,0);
$colors[] = imagecolorallocate($img,55,55,55);
imagefilledrectangle($img,1,1,$x-2,$y-2,$bg);
imagerectangle($img,0,0,$x-1,$y-2,$border);
for ($i=0;$i<strlen($code);$i++)
{
	$color = $colors[$i % count($colors)];
	imagettftext($img,28+rand(0,8),-20+rand(0,40),($i+0.3)*$space,50+rand(0,10),$color,'../src/ALBA.TTF',$code{$i});
}

for($i=0;$i<400;$i++)
{
	$x1 = rand(3,$x-3);
	$y1 = rand(3,$y-3);
	$x2 = $x1-2-rand(0,8);
	$y2 = $y1-2-rand(0,8);
	imageline($img,$x1,$y1,$x2,$y2,$colors[rand(0,count($colors)-1)]);
}

imagepng($img);

?>