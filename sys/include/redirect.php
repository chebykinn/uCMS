<?php
$url = $_SERVER['REQUEST_URI'];
if(NICE_LINKS){
	$url = preg_replace('#(/redirect/)#', '',$url);
}else{
	$url = $_GET['url'];
}
if(preg_match('#(http?|ftp|https):/\S+[^\s.,>)\];\'\"!?]#i',$url)){
	echo 'Перенаправляю на '.$url;
	sleep(1);
	header("Location: ".$url."");
	exit();
}
?>