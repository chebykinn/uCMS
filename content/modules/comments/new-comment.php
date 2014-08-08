<?php
require 'manage-comments.php';
if(isset($_POST['comment']) and preg_match("#(".SITE_DOMAIN.")#", $_SERVER['HTTP_REFERER'])){
	$post = $udb->parse_value($_POST['post']);
	$comment = parse_comment($_POST['comment']);
	if($comment != ''){
		$author = $user->get_user_id(); 
		$test = $udb->get_row("SELECT `id`, `comment` FROM `".UC_PREFIX."comments` WHERE `post` = '$post' AND `comment` = '$comment' AND `author` = '$author'");
		if ( !empty($test['id']) ) {
			require get_module("path", "comments").'templates/duplicate-comment.php';
			exit;
		} else {
			$add = add_comment($_POST);
			switch ($add) {
				case 1:
					require get_module("path", "comments").'templates/empty-comment.php';
				exit;
				
				case 2:
					require get_module("path", "comments").'templates/not-allowed-comment.php';
				exit;

				case 3:
					require get_module("path", "comments").'templates/wrong-code-comment.php';
				exit;

				default:
					header("Location: ".$ucms->get_back_url()."#comment-added");
				break;
			}
		}
	}else{ 
		require get_module("path", "comments").'templates/empty-comment.php';
		exit;
	}
	
}else{
	header("Location: ".$ucms->get_back_url());
}
?>
