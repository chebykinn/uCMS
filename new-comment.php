<?php
require 'config.php';
require 'admin/manage-comments.php';
if(isset($_POST['comment']) and preg_match("#(".SITE_DOMAIN.")#", $_SERVER['HTTP_REFERER'])){
	$post = $udb->parse_value($_POST['post']);
	$comment = parse_comment($_POST['comment']);
	if($comment != ''){
		$author = $user->get_user_id(); 
		$test = $udb->get_row("SELECT `id`, `comment` FROM `".UC_PREFIX."comments` WHERE `post` = '$post' AND `comment` = '$comment' AND `author` = '$author'");
		if ( !empty($test['id']) ) {
			require GENERAL_TEMPLATES_PATH.'duplicate-comment.php';
			exit;
		} else {
			$add = add_comment($_POST);
			switch ($add) {
				case 1:
					require GENERAL_TEMPLATES_PATH.'empty-comment.php';
				exit;
				
				case 2:
					require GENERAL_TEMPLATES_PATH.'not-allowed-comment.php';
				exit;

				case 3:
					require GENERAL_TEMPLATES_PATH.'wrong-code-comment.php';
				exit;

				default:
					header("Location: ".$ucms->get_back_url());
				break;
			}
		}
	}else{ 
		require GENERAL_TEMPLATES_PATH.'empty-comment.php';
		exit;
	}
	
}
?>
