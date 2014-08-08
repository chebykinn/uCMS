<table class="info" id="new-materials">
	<tr>
		<th><?php $ucms->cout("widget.new_materials.posts.header"); ?></th>
		<th><?php $ucms->cout("widget.new_materials.comments.header"); ?></th>
		<th><?php $ucms->cout("widget.new_materials.pages.header"); ?></th>
		<th><?php $ucms->cout("widget.new_materials.users.header"); ?></th>
	</tr>
	<?php
	$lim = 10;
	if(!$user->has_access("at_least_one", 4)){ 
		$publish = "WHERE `p`.`publish` > 0";
		$approved = "WHERE `c`.`approved` > 0";
		$activation = "WHERE `u`.`activation` > 0";
	}else{
		$publish = "";
		$approved = "";
		$activation = "";
	}
	$posts = $udb->get_rows("SELECT `p`.`id`, `p`.`title`, `p`.`alias`, `p`.`author`, `p`.`category`, `p`.`date`,
	`u`.`login` AS `author_login`, `u`.`group`, `uf`.`value` AS `nickname`, `c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p`
	 FORCE INDEX (PRIMARY)
	 LEFT JOIN `".UC_PREFIX."users` AS `u` ON `p`.`author` = `u`.`id` 
	 LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
	 LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category` 
	 $publish ORDER BY `p`.`id` DESC LIMIT $lim");

	$comments = $udb->get_rows("SELECT `c`.`id` AS `cid`, `c`.`comment`, `c`.`author` AS `cauthor`, `c`.`post`,
	`u`.`login`, `u`.`group`, `uf`.`value` AS `nickname`,
	`p`.`id`, `p`.`title`, `p`.`alias`, `p`.`date`, `p`.`category`, `p`.`author`, `uu`.`login` AS `author_login`,
	`ca`.`name` AS `category_name`, `ca`.`alias` AS `category_alias` FROM `".UC_PREFIX."comments` AS `c`  FORCE INDEX (PRIMARY)
	LEFT JOIN `".UC_PREFIX."users` AS `u` ON `c`.`author` = `u`.`id` 
	LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname' 
	INNER JOIN `".UC_PREFIX."posts` AS `p` ON `p`.`id` = `c`.`post`
	LEFT JOIN `".UC_PREFIX."users` AS `uu` ON `p`.`author` = `uu`.`id` 
	LEFT JOIN `".UC_PREFIX."categories` AS `ca` ON `ca`.`id` = `p`.`category`
	$approved ORDER BY `c`.`id` DESC LIMIT $lim");

	$pages = $udb->get_rows("SELECT `p`.`id`, `p`.`title`, `p`.`alias`, `p`.`author`, `p`.`date`, `p`.`parent`,
	`u`.`login`, `u`.`group`, `uf`.`value` AS `nickname`, `pa`.`id` AS `parent_id`, `pa`.`alias` AS `parent_alias`, `pa`.`title` AS `parent_title` FROM `".UC_PREFIX."pages` AS `p`
	FORCE INDEX (PRIMARY)
	LEFT JOIN `".UC_PREFIX."users` AS `u` ON `p`.`author` = `u`.`id` 
	LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
	LEFT JOIN `".UC_PREFIX."pages` AS `pa` ON `pa`.`id` = `p`.`parent`
	$publish ORDER BY `p`.`id` DESC LIMIT $lim");
	
	$users = $udb->get_rows("SELECT `u`.`id`, `u`.`login`, `u`.`group`, `uf`.`value` AS `nickname` FROM `".UC_PREFIX."users` AS `u` FORCE INDEX (PRIMARY)
	LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
	$activation ORDER BY `u`.`id` DESC LIMIT $lim");

	if($posts or $comments or $pages or $users){
		$arr = array(count($posts), count($comments), count($pages), count($users));
		sort($arr);
		//***
		for ($i = 0; $i < $arr[3]; $i++) { 
			echo "<tr>";
			if(isset($posts[$i]['id'])){ //posts

				if($user->get_user_id() == $posts[$i]['author']){
					$accessLVL = 2;
				}else if($posts[$i]['group'] == 1){
					$accessLVL = 6;
				}else 
					$accessLVL = 4;
			
				if(NICE_LINKS){
					$slink = post_sef_links($posts[$i]);
				}else $slink = UCMS_DIR.'/?id='.$posts[$i]['id'];

				$link = "<a href=\"$slink\" target=\"_blank\">".htmlspecialchars($posts[$i]['title'])."</a>";

				echo "<td><b>".$posts[$i]['category_name'].":</b> <div class=\"title\">$link</div> (";
				if ((int) $posts[$i]['author'] == 0)
					echo $posts[$i]['author'].")";
				else 
					echo (!empty($posts[$i]['nickname']) ? $posts[$i]['nickname'] : $posts[$i]['author_login']).")";
				echo "<span class=\"actions\"> ";
				if($user->has_access("posts", $accessLVL)){
					echo "<a href=\"manage.php?module=posts&amp;action=update&amp;id=".$posts[$i]['id']."\">".$ucms->cout("widget.new_materials.edit.button", true)."</a>";
				}
				if($user->has_access("posts", $accessLVL+1)){
					echo "&nbsp;|&nbsp;<a href=\"manage.php?module=posts&amp;action=delete&amp;id=".$posts[$i]['id']."\">".$ucms->cout("widget.new_materials.delete.button", true)."</a>";
				}
				echo "</span></td>";
			}else{
				echo "<td></td>";
			} //end-posts
			if(isset($comments[$i]['cid'])){ //comments
				if($user->get_user_id() == $comments[$i]['cauthor']){
					$accessLVL = 3;
				}else if($comments[$i]['group'] == 1){
					$accessLVL = 6;
				}else 
					$accessLVL = 4;
				if(NICE_LINKS){
					$slink = post_sef_links($comments[$i]);
				}else 
					$slink = UCMS_DIR.'/?id='.$comments[$i]['post'];
				$limit = 100; //ограничение текста длинных комментариев
				$comment = $comments[$i]['comment'];
				if(mb_strlen($comment) > $limit){
					$comment = htmlspecialchars(mb_substr($comment, 0, $limit, 'UTF-8'))."...";
				}else{
					$comment = htmlspecialchars($comment);
				}
				$link = "<a href=\"$slink#comment-".$comments[$i]['cid']."\" target=\"_blank\">".$comment."</a>";
				echo "<td><div class=\"title\">".$link."</div>(";
				if ((int) $comments[$i]['cauthor'] == 0){
					echo $comments[$i]['cauthor'].")";
				}
				else{
					echo (!empty($comments[$i]['nickname']) ? $comments[$i]['nickname'] : $comments[$i]['login']).")";
				}
				echo "<span class=\"actions\"> ";
				if($user->has_access("comments", $accessLVL)){
					echo "<a href=\"manage.php?module=comments&amp;action=update&amp;id=".$comments[$i]['cid']."\">".$ucms->cout("widget.new_materials.edit.button", true)."</a>";
				}
				if($user->has_access("comments", $accessLVL+1)){
					echo "&nbsp;|&nbsp;<a href=\"manage.php?module=comments&amp;action=delete&amp;id=".$comments[$i]['cid']."\">".$ucms->cout("widget.new_materials.delete.button", true)."</a>";
				}
				echo "</span></td>";
			}else{
				echo "<td></td>";
			} //end-comments
			if(isset($pages[$i]['id'])){ //pages
				if($user->get_user_id() == $pages[$i]['author']){
					$accessLVL = 2;
				}else if($pages[$i]['group'] == 1){
					$accessLVL = 6;
				}else 
					$accessLVL = 4;
				
				$link = NICE_LINKS ? page_sef_links($pages[$i]) : UCMS_DIR.'/?p='.$pages[$i]['id'];
				$link = "<a href=\"$link\" target=\"_blank\">".$pages[$i]['title']."</a>";		
				echo "<td><div class=\"title\">".$link."</div> (";

				if ((int) $pages[$i]['author'] == 0)
					echo $pages[$i]['author'].")";
				else 
					echo (!empty($pages[$i]['nickname']) ? $pages[$i]['nickname'] : $pages[$i]['login']).")";
				echo "<span class=\"actions\"> ";
				if($user->has_access("pages", $accessLVL)){
					echo "<a href=\"manage.php?module=pages&amp;action=update&amp;id=".$pages[$i]['id']."\">".$ucms->cout("widget.new_materials.edit.button", true)."</a>";
				}
				if($user->has_access("pages", $accessLVL+1)){
					echo "&nbsp;|&nbsp;<a href=\"manage.php?module=pages&amp;action=delete&amp;id=".$pages[$i]['id']."\">".$ucms->cout("widget.new_materials.delete.button", true)."</a>";
				}
				echo "</span></td>";
			}else{
				echo "<td></td>";
			} //end-pages
			if(isset($users[$i]['id'])){ //users
				if($user->get_user_id() == $users[$i]['id']){
					$accessLVL = 2;
				}else if($users[$i]['group'] == 1){
					$accessLVL = 6;
				}else 
					$accessLVL = 4;
				$link = NICE_LINKS ? UCMS_DIR.'/user/'.$users[$i]['login'] : UCMS_DIR.'/?action=profile&amp;'.$users[$i]['id'];
				$link = "<a href=\"$link\" target=\"_blank\">".$users[$i]['login'].(!empty($users[$i]['nickname']) ? " (".$users[$i]['nickname'].")" : "")."</a>";
				echo "<td><div class=\"title\">".$link."</div>";
				echo "<span class=\"actions\"> ";
				if($user->has_access("users", $accessLVL)){
					echo "<a href=\"manage.php?module=users&amp;action=update&amp;id=".$users[$i]['id']."\">".$ucms->cout("widget.new_materials.edit.button", true)."</a>";
				}
				if($user->has_access("users", $accessLVL+1) and $users[$i]['id'] > 1){
					echo "&nbsp;|&nbsp;<a href=\"manage.php?module=users&amp;action=delete&amp;id=".$users[$i]['id']."\">".$ucms->cout("widget.new_materials.delete.button", true)."</a>";
				}
				echo "</span></td>";
			}else{
				echo "<td></td>";
			} //end-users
			echo "</tr>";
		}
	}
?>
</table>