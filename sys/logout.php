<?php
if(!$user->logged()){
	header("Location: ". $user->get_back_url()); 
	exit;
}
$user->logout();
header("Location:".$user->get_back_url());
exit;
?>
