<?php
/**
 *
 * Posts API
 * @package uCMS Posts
 * @since uCMS 1.0
 * @version uCMS 1.3
 *
*/
if(!isset($no_query)){
	$id = isset($id) ? $id : 0;
	if($id <= 0){
		if(!isset($post_sql)){
			$post_sql = "SELECT `p`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname`,
			`c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p` FORCE INDEX (PRIMARY)
			LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
			LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
			LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category`
			WHERE `p`.`publish` > 0 ORDER BY `p`.`date` DESC, `p`.`id` DESC LIMIT 0, ".POSTS_ON_PAGE;
		}
		if(!isset($results))
			$posts = $udb->get_rows($post_sql);
		else
			$posts = $results;

		if($posts and count($posts) > 0){
			$posts_count = count($posts);
			$pinned = array();
			if($action == 'index' or $action == 'page'){
				$pinned = $udb->get_rows("SELECT `p`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname`,
				`c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p` FORCE INDEX (PRIMARY)
				LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
				LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
				LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category`
				WHERE `p`.`publish` = 2 ORDER BY `p`.`date` DESC, `p`.`id` DESC LIMIT 100");
			}


			if($pinned and count($pinned) > 0){ 
				$pinned_count = count($pinned);
			}else{
				$pinned_count = 0;
				$pinned = array();
			}
			$pinned_posts_started = false;
			$pinned_list = array();
			$posts = array_merge($pinned, $posts);
			$posts_count += $pinned_count;
		}else{
			$posts_count = 0;
		}

	}else{
		if(!$user->has_access("posts", 4))
			$id_post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '$id' and publish > 0 LIMIT 1";
		else $id_post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1";
		$id_post = $udb->get_row($id_post_sql);
		if(!empty($id_post['id'])){
			$posts_count = 1;
		}else{
			$ucms->panic(404);
		}
		define("POST_AUTHOR", get_post_author());
	}
}
	/**
	 *
	 * Check if there are posts on the site
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_posts(){
		global $posts_count, $user;
		if($posts_count != 0 and $user->has_access("posts", 1)){ 
			return true;
		}
		return false;	
	}

	/**
	 *
	 * Check if comments enabled for current post at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_comments_enabled($post = -1, $posts = array()){
		global $id;
		if($id != ''){
			global $id_post_query, $id_post;
			if($id_post['comments'] < 0){
				return false;
			}else{
				return true;
			}
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;

			if($posts[$post]['comments'] < 0){
				return false;
			}else{
				return true;
			}
		}
	}

	/**
	 *
	 * Check if it is post page
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function post_page(){
		global $id, $user;
		if ($id != '' and $user->has_access("posts", 1)){
			return true;
		}
		return false;	
	}
	
	/**
	 *
	 * Return number of posts at current page
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function posts_count(){
		global $posts_count;
		return $posts_count;	
	}
	
	/**
	 *
	 * Return number of all posts
	 * @package uCMS Posts
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function all_posts_count(){
		global $count, $udb;
		if(!isset($count)){
			$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 1");
		}
		return $count;
	}

	/**
	 *
	 * Print current post id at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_id($post = -1, $posts = array()){
		global $id;
		if($id != ''){
			echo $id;
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			echo $posts[$post]['id'];
		}
	}
	
	/**
	 *
	 * Return current post id at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_post_id($post = -1, $posts = array()){
		if($post == -1)
			global $post;
		if(!isset($posts[0]))
			global $posts;
		return $posts[$post]['id'];
	}
		
	/**
	 *
	 * Return current post alias at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_post_alias($post = -1, $posts = array()){
		if(NICE_LINKS){
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			$slink = post_sef_links($posts[$post]);
			return $slink;
		}
		else{
			return UCMS_DIR.'/?id='.get_post_id();
		}
	}

	/**
	 *
	 * Print current post alias at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_alias($post = -1, $posts = array()){
		if(NICE_LINKS){		
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;

			$slink = post_sef_links($posts[$post]);
			echo $slink;
		}
		else{
			echo UCMS_DIR.'/?id='.get_post_id();
		}
	}
	
	/**
	 *
	 * Print current post category alias at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_category_alias($post = -1, $posts = array()){
		global $id, $udb;
		if($id != ''){
			global $id_post_query, $id_post;
			if(NICE_LINKS){
				$category = $udb->get_row("SELECT `alias` FROM `".UC_PREFIX."categories` WHERE `id` = '$id_post[category]' LIMIT 1");
				echo UCMS_DIR."/".CATEGORY_SEF_PREFIX."/$category[alias]";
			}else{
				echo UCMS_DIR."/?category=$id_post[category]";
			}
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			if(NICE_LINKS){
				echo UCMS_DIR."/".CATEGORY_SEF_PREFIX."/".$posts[$post]['category_alias'];
			}else{
				echo UCMS_DIR."/?category=".$posts[$post]['category'];
			}
		}
	}
	
	/**
	 *
	 * Print current post category name at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_category($post = -1, $posts = array()){
		global $id, $udb;
		if($id != ''){
			global $id_post_query, $id_post;
			$sql = "SELECT `name` FROM `".UC_PREFIX."categories` WHERE id = '$id_post[category]'";
			$category = $udb->get_row($sql);
			echo $category['name'];
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			echo $posts[$post]['category_name'];
		}
	}
	
	/**
	 *
	 * Print current post title at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_title($post = -1, $posts = array()){
		global $id, $ucms;
		if($id != ''){
			global $id_post_query, $id_post;
			echo stripcslashes($id_post['title']);
			if($id_post['publish'] < 1) echo " (".$ucms->cout("module.posts.draft.label", true).")";
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			echo stripcslashes($posts[$post]['title']);
		}
	}

	/**
	 *
	 * Print current post content at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_content($post = -1, $posts = array()){
		global $id, $ucms;
		if($id != ''){
			global $id_post_query, $id_post;
			if(preg_match("#(@-more-@)#", $id_post['body'])){
				$da_post = explode('@-more-@', $id_post['body'], 2);
				if(isset($da_post[1])){
					$param = explode("-@", $da_post[1], 3);
					$class = $param[0];
					if(!isset($param[2])){
						$str = "$param[0]-@";
					}else{
						$str = "$class-@$param[1]-@";
					}
					
					$da_post[1] = str_replace("$str", "", $da_post[1]);
					$body = $da_post[0].$da_post[1];
					echo stripcslashes($body);
				}
			}else{
				echo stripcslashes($id_post['body']);
			}
		}else{
			global $results_body, $p, $pinned;		
			if(isset($results_body) and isset($p)){
				$post_body = $results_body[$p];
			}else{
				if($post == -1)
					global $post;
				if(!isset($posts[0]))
					global $posts;
				$post_body = $posts[$post]['body'];
			}
			if(preg_match("#(<!--more-->)#", $post_body)){
				$da_post = explode('<!--more-->', $post_body);
				echo $da_post[0].'...<br><br><a href="'.get_post_alias().'" class="more-link">'.$ucms->cout("module.posts.more_link.label", true).'</a>';
			}elseif(preg_match("#(@-more-@)#", $post_body)){
				$da_post = explode('@-more-@', $post_body);
				if(isset($da_post[1])){
					$param = explode("-@", $da_post[1], 3);
					$class = $param[0];
					if(!isset($param[2])){
						echo stripcslashes($da_post[0]).'<br><br><a href="'.get_post_alias().'" class="more-link">'.$param[0].'</a>';
					}else
						echo stripcslashes($da_post[0]).'<br><br><a href="'.get_post_alias().'" class="'.$class.'">'.$param[1].'</a>';
				}
				
			}else echo stripcslashes($post_body);
		}
	}
	
	/**
	 *
	 * Print current post author link or just $plain author name at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/	
	function post_author($plain = false, $post = -1, $posts = array()){
		global $id, $user;		
		if($id != ''){
			global $id_post_query, $id_post;
			if($plain or (int) $id_post['author'] == 0){
				echo $id_post['author'];
			}else{
				if($id_post['author'] != $user->get_user_id()){
					$user_login = $user->get_user_login($id_post['author']);
					$user_nickname = $user->get_user_nickname($id_post['author']);
				}
				else {
					$user_login = $user->get_user_login();
					$user_nickname = $user->get_user_nickname();
				}

				$link = NICE_LINKS ? UCMS_DIR."/user/$user_login" : UCMS_DIR."/?action=profile&amp;id=$id_post[author]";
				echo "<a href=\"$link\">".(!empty($user_nickname) ? $user_nickname : $user_login)."</a>";
			}
			
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;	
			$post_author = $posts[$post]['author'];
			if($plain or (int) $post_author === 0){
				echo $post_author;
			}else{
				$user_login = $posts[$post]['author_login'];
				$user_nickname = $posts[$post]['author_nickname'];
				$link = NICE_LINKS ? UCMS_DIR."/user/$user_login" : UCMS_DIR."/?action=profile&amp;id=$post_author";
				echo "<a href=\"$link\">".(!empty($user_nickname) ? $user_nickname : $user_login)."</a>";
			}
			
		}
	}

	/**
	 *
	 * Return current post author name at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_post_author($post = -1, $posts = array()){
		global $id, $user;		
		if($id != ''){
			global $id_post_query, $id_post;
			if((int) $id_post['author'] == 0){
				return $id_post['author'];
			}else{
				$user_login = $user->get_user_login($id_post['author']);
				return $user_login;
			}
			
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;	
			$post_author = $posts[$post]['author'];
			if((int) $post_author == 0){
				return $post_author;
			}else{
				$user_login = $posts[$post]['author_login'];
				return $user_login;
			}
			
		}
	}
	
	/**
	 *
	 * Print current post date at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_date($post = -1, $posts = array()){
		global $id, $ucms;
		if($id != ''){
			global $id_post_query, $id_post;
			$date_str = $ucms->date_format($id_post['date'], DATE_FORMAT);
			echo $date_str;
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			$date_str = $ucms->date_format($posts[$post]['date'], DATE_FORMAT);
			echo $date_str;
		}
	}

	/**
	 *
	 * Return current post date at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_post_date($post = -1, $posts = array()){
		global $id, $ucms;
		if($id != ''){
			global $id_post_query, $id_post;
			return $id_post['date'];
		}else{
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			return $posts[$post]['date'];
		}
	}
	
	/**
	 *
	 * Print current post tags at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_tags($post = -1, $posts = array()){
		global $id, $id_post, $ucms;
		if($id != ''){
			$keywords = explode(', ', $id_post['keywords']);
			if ( count($keywords) > 0 and $keywords[0] != '' ) {
				foreach ( $keywords as $keyword ) {
					$keyalias = preg_replace('/\s/', '%20', $keyword);
					$link = NICE_LINKS ? '<a href="'.UCMS_DIR.'/'.TAG_SEF_PREFIX.'/'.$keyalias.'" >'.$keyword.'</a> ' : '<a href="/?action='.TAG_SEF_PREFIX.'&amp;key='.$keyalias.'" >'.$keyword.'</a> ';
					echo $link;
				}
			}else{
				$ucms->cout("module.posts.no_tags.label");
			}
		}else{			
			if($post == -1)
				global $post;
			if(!isset($posts[0]))
				global $posts;
			$keywords = explode(', ', $posts[$post]['keywords']);

			if ( count($keywords) > 0 and $keywords[0] != '' ) {
				foreach ( $keywords as $keyword )	{
					$keyalias = preg_replace("#(<span style='color:black; background: yellow;'>)#", '', trim($keyword));
					$keyalias = preg_replace("#(</span>)#", '', $keyalias);
					$keyalias = preg_replace('/\s/', '%20', $keyalias);
					$link = NICE_LINKS ? '<a href="'.UCMS_DIR.'/'.TAG_SEF_PREFIX.'/'.$keyalias.'" >'.$keyword.'</a> ' : '<a href="/?action='.TAG_SEF_PREFIX.'&amp;key='.$keyalias.'" >'.$keyword.'</a> ';
					echo $link;
				}
			}else $ucms->cout("module.posts.no_tags.label");
		}
		
	}

	/**
	 *
	 * Print all comments for current post at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_comments($count, $empty, $closed, $post = -1, $posts = array()){
		global $id;
		if(is_activated_module('comments') and COMMENTS_ENABLED){
			if($id != ''){
				global $id_post_query, $id_post;
				if(is_comments_enabled()){
					if($id_post['comments'] > 0):
						echo $count.'('.$id_post['comments'].')';
					else:
						echo $empty;
					endif;
				}else{
					echo $closed;
				}
				
			}else{		
				if($post == -1)
					global $post;
				if(!isset($posts[0]))
					global $posts;
				if(is_comments_enabled()){
					if($posts[$post]['comments'] > 0):
						echo '<a href="'.get_post_alias().'#comments">'.$count.'('.$posts[$post]['comments'].')</a>';
					else:
						echo '<a href="'.get_post_alias().'#comments">'.$empty.'</a>';
					endif;
				}else{
					echo $closed;
				}
				
			}
		}else return false;
	}
	
	/**
	 *
	 * Print manage tools for current post at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function post_admin($post = -1, $posts = array()){
		global $id, $user, $id_post, $ucms;
		if($user->has_access("posts", 2)){	
			if($id != ''){
				if($id_post['author'] == $user->get_user_id())
					$accessLVL = 2;
				elseif($user->get_user_group($id_post['author']) == ADMINISTRATOR_GROUP_ID){
					$accessLVL = 6;
				}else
					$accessLVL = 4;
				if($user->has_access("posts", $accessLVL)){
					echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=posts&amp;action=update&amp;id='.$id.'" >'.$ucms->cout("module.posts.post.edit.button", true).'</a>';
				}
			}else{
				if($post == -1)
					global $post;
				if(!isset($posts[0]))
					global $posts;
				if(isset($posts[$post]['author_group']))
					$group = $posts[$post]['author_group'];
				else
					$group = GUEST_GROUP_ID;
				if($posts[$post]['author'] == $user->get_user_id())
					$accessLVL = 2;
				elseif($group == ADMINISTRATOR_GROUP_ID){
					$accessLVL = 6;
				}else
					$accessLVL = 4;
				if($user->has_access("posts", $accessLVL)){
					echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=posts&amp;action=update&amp;id='.get_post_id().'" >'.$ucms->cout("module.posts.post.edit.button", true).'</a>';
				}
	
			}
		}
	}

	/**
	 *
	 * Get alias of post by its id
	 * @package uCMS Posts
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_alias($id){
		global $udb;
		if(NICE_LINKS){
			$alias = $udb->get_row("SELECT `alias` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1");
			return $alias;
		}
	}

	/**
	 *
	 * Check if there are pinned posts
	 * @package uCMS Posts
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_pinned_posts(){
		global $pinned, $pinned_count, $action;
		if($pinned and $pinned_count > 0){ 
			if($action == 'index' or $action == 'page')
				return true;
		}
		return false;
	}

	/**
	 *
	 * Return number of pinned posts
	 * @package uCMS Posts
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function count_pinned(){
		global $pinned_count;
		return $pinned_count;
	}

	/**
	 *
	 * Check if current post is pinned at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_pinned($post = -1, $posts = array()){
		if($post == -1)
			global $post;
		if(!isset($posts[0]))
			global $posts;
		return $posts[$post]['publish'] == 2;
	}

	/**
	 *
	 * Get post data from $column by post id or from prepared array by iterator $post
	 * @package uCMS Posts
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_post($column, $id = 0, $post = -1){
		$columns = array("id", "title", "body", "keywords", "publish", "alias", "author",
		"category", "comments", "date", "category_name", "category_alias", "author_login", "author_group");
		
		if(in_array($column, $columns)){
			if($id <= 0){
				global $posts;
				if($post == -1){
					global $post;
				}
				if(isset($posts) and is_array($posts) and count($posts) > 0 and isset($posts[$post][$column])){
					return $posts[$post][$column];
				}
			}else{
				global $udb;
				$column = $udb->parse_value($column);
				$id = (int) $id;
				$post = $udb->get_row("SELECT `$column` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1");
				if($post) return $post[$column];
			}
		}
		return false;
	}

	/**
	 *
	 * Prepare for pinned posts listing
	 * @package uCMS Posts
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function pinned_posts_started(){
		global $pinned_list, $pinned_posts_started;
		$pinned_list = array();
		$pinned_posts_started = true;
	}

	/**
	 *
	 * Mark pinned post that had been shown already at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function pinned_post($post = -1, $posts = array()){
		global $pinned_list;
		if($post == -1)
			global $post;
		if(!isset($posts[0]))
			global $posts;
		if( is_pinned($post, $posts) and !in_array(get_post_id($post, $posts), $pinned_list) ){
			$pinned_list[] = get_post_id($post, $posts);
		}
	}

	/**
	 *
	 * Check if current post is listing in pinned ones at iterator $post from prepared array or given array $posts
	 * @package uCMS Posts
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function listing_pinned_post($post = -1, $posts = array()){
		global $pinned_list;
		if($post == -1)
			global $post;
		if(!isset($posts[0]))
			global $posts;
		if( is_pinned($post, $posts) and !in_array(get_post_id($post, $posts), $pinned_list) ){
			return true;
		}else return false;
	}

	/**
	 *
	 * Check if pinned posts ended
	 * @package uCMS Posts
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function pinned_posts_ended(){
		global $pinned_count, $pinned_list, $pinned_posts_started;
		if( $pinned_count == count($pinned_list) and $pinned_count > 0 and $pinned_posts_started ){
			$pinned_posts_started = false;
			return true;
		}
	}
?>