<?php
/*
Пример виджета для сайта. Выводит последние одобренные комментарии к постам.
*/
$comments = $udb->get_rows("SELECT * FROM `".UC_PREFIX."comments` WHERE `approved` = '1' ORDER BY `date` DESC LIMIT 3"); //по умолчанию выводит только 4 комментария, но можно увеличить лимит
if(!$comments)
	echo 'Комментариев пока нет';
else{ 
	for($i = 0; $i < count($comments); $i++){
		echo '<div id="ncomment-'.$comments[$i]['id'].'" class="precomm">';
		if((int) ($comments[$i]['author']) === 0){
			$plain = true;
		}else $plain = false;
		if(USER_AVATARS){ //если включены аватары
			if(!$plain)
				echo '<img style="border-radius: 2px;" src="'.UCMS_DIR."/".AVATARS_PATH.$user->get_user_avatar(0, $user->get_user_login($comments[$i]['author'])).'" width="32" height="32" alt="">';
			else 
				echo '<img style="border-radius: 2px;" src="'.UCMS_DIR."/".AVATARS_PATH.'no-avatar.jpg" width="32" height="32" alt="">';
		}
		if(!$plain)
			echo ' <b>'.$user->get_user_login($comments[$i]['author']).'</b> сказал:<br>';
		else
			echo ' <b>'.$comments[$i]['author'].'</b> сказал:<br>';
		$limit = 60; //ограничение текста длинных комментариев
		$comment = $comments[$i]['comment'];
		if(mb_strlen($comment) > $limit){
			echo htmlspecialchars(mb_substr($comment, 0, $limit, 'UTF-8'))."...";
		}else{
			echo htmlspecialchars($comment);
		}
		if(NICE_LINKS){
			$post = $udb->get_row("SELECT `alias` FROM `".UC_PREFIX."posts` WHERE `id` = '".$comments[$i]['post']."' LIMIT 1");
			echo ' <a style="font-size: 8pt; color: #fff" href="/'.$post['alias'].'#comment-'.$comments[$i]['id'].'" title="">[к комментарию]</a></div>';
		}else{
			echo ' <a style="font-size: 8pt; color: #fff" href="/?id='.$comments[$i]['post'].'#comment-'.$comments[$i]['id'].'" title="">[к комментарию]</a></div>';
		}
		
	}
}

?>	
