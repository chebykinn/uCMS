<?php
if(!$user->logged()){
	header("Location: ". $ucms->get_back_url()); 
	exit;
}
$user->logout();
header("Location:".$ucms->get_back_url());
exit;
?>
