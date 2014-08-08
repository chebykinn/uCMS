<?php
if(!isset($no_query)){
	$id = isset($id) ? $id : 0;
	if($id <= 0){
		if(!isset($post_sql)) $post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` > 0 ORDER BY `id` DESC LIMIT 0, ".POSTS_ON_PAGE;
		$posts = $udb->get_rows($post_sql);
		if($posts){
			$categories = $udb->get_rows("SELECT * FROM `".UC_PREFIX."categories` ORDER BY `id` DESC");
			$post_count = count($posts);

			$pinned = $udb->get_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 2 ORDER BY `id` DESC");

			if($pinned) $pinned_count = count($pinned);
			else $pinned_count = 0;
			for($z = 0; $z < $pinned_count; $z++){
				for($j = 0; $j < count($categories); $j++){
					if($categories[$j]['id'] == $pinned[$z]['category']){
						$pinned_cat_names[] = $categories[$j]['name'];
						$pinned_cat_aliases[] = $categories[$j]['alias'];
					}
				}
			}
		}else{ 
			$post_count = 0;
			$pinned_count = 0;
			$pinned = false;
		}
		if($post_count != 0){ 
			for($i = 0; $i < count($posts); $i++){
				$ids[] = $posts[$i]['id'];
				$aliases[] = $posts[$i]['alias'];
				$cat_ids[] = $posts[$i]['category'];
				$titles[] = $posts[$i]['title'];
				$bodies[] = $posts[$i]['body'];
				$authors[] = $udb->parse_value($posts[$i]['author']);
				$p_comments[] = $posts[$i]['comments'];
				$post_tags[] = $posts[$i]['keywords'];
				$dates[] = $posts[$i]['date'];
				for($j = 0; $j < count($categories); $j++){
					if($categories[$j]['id'] == $posts[$i]['category']){
						$cat_names[] = $categories[$j]['name'];
						$cat_aliases[] = $categories[$j]['alias'];
					}
					
				}
			}
			$authors2 = implode("','", $authors);
			$authors2 = "'".$authors2."'";
			$logins = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($authors2) ");
		}
	}else{
		if(!$user->has_access(1, 4))
			$id_post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '$id' and publish > 0 LIMIT 1";
		else $id_post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1";
		$id_post = $udb->get_row($id_post_sql);
		if(!empty($id_post['id'])){
			$post_count = 1;
		}else{
			$ucms->panic(404);
		}
		
	}
}

	function is_posts(){
		global $post_count, $user;
		if($post_count != 0 and $user->has_access(1, 1)){ 
			return true;
		}
		return false;	
	}

	function is_comments_enabled(){
		global $id;
		if($id != ''){
			global $id_post_query, $id_post;
			if($id_post['comments'] < 0){
				return false;
			}else{
				return true;
			}
		}else{
			global $results_comment, $p, $pinned;		
			if(isset($results_comment) and isset($p)){
				$post_comments = $results_comment[$p];
			}else{
				global $p_comments, $post, $pin;
				$post_comments = $p_comments[$post];
				if(isset($pinned) and $post_comments == '' or $pin){
					$post_comments = $pinned[$post]['comments'];
				}
			}
			if($post_comments < 0){
				return false;
			}else{
				return true;
			}
		}
	}

	function post_page(){
		global $id, $user;
		if ($id != '' and $user->has_access(1, 1)){
			return true;
		}
		return false;	
	}
		
	function posts_count(){
		global $post_count;
		return $post_count;	
	}
	
	function all_posts_count(){
		global $count, $udb;
		if(!isset($count)){
			$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 1");
		}
		return $count;
	}
	function post_id(){
		global $id;
		if($id != ''){
			echo $id;
		}else{
			global $results_id, $p, $pinned;
			if(isset($results_id) and isset($p)){
				$post_id = $results_id[$p];
			}else{
				global $ids, $post, $pin;
				$post_id = $ids[$post];
				if(isset($pinned) and empty($post_id) or $pin){
					$post_id = $pinned[$post]['id'];
				}
			}
			echo $post_id;
		}
	}
		
	function get_post_id(){
		global $results_id, $p, $pinned;
		if(isset($results_id) and isset($p)){
			$post_id = $results_id[$p];
		}else{
			global $ids, $post, $pin;	
			$post_id = $ids[$post];	
			if(isset($pinned) and empty($post_id) or $pin){
				$post_id = $pinned[$post]['id'];
			}
		}
		if(isset($post_id))
			return $post_id;
		else return false;
	}
		
				
	function get_post_alias(){
		global $aliases, $post;
		if(NICE_LINKS){
			$link = explode("@", POST_SEF_LINK);
			global $aliases, $ids, $dates, $titles, $cat_names, $cat_aliases, $authors, $post, $user, $logins;
			global $results_alias, $results_date, $results_id, $results_title, $results_author, $results_category_aliases, $results_category_names, $p, $pinned, $pinned_cat_aliases, $pinned_cat_names, $pin;
			if(isset($results_alias) and isset($p)){
				$post_alias = $results_alias[$p];
				$post_id = $results_id[$p];
				$post_date = $results_date[$p];
				$post_title = $results_title[$p];
				$post_category = $results_category_names[$p];
				$post_category_alias = $results_category_aliases[$p];
				$post_author = $results_author[$p];
			}else{
				$post_alias = $aliases[$post];
				$post_id = $ids[$post];
				$post_date = $dates[$post];
				$post_title = $titles[$post];
				$post_category = $cat_names[$post];
				$post_category_alias = $cat_aliases[$post];
				for($i = 0; $i < count($logins); $i++){
					if(isset($author_id) and ($authors[$post] === $author_id)) break;
					if($authors[$post] === $logins[$i]['id']){
						$author_id = $logins[$i]['id'];
						$post_author = $authors[$post];
						$user_login = $logins[$i]['login'];
						break;
					}
				}
				if(!isset($post_author))
					$post_author = $authors[$post];
				if(isset($pinned) and empty($post_alias) or $pin){
					$post_alias = $pinned[$post]['alias'];
					$post_id = $pinned[$post]['id'];
					$post_date = $pinned[$post]['date'];
					$post_title = $pinned[$post]['title'];
					$post_category = $pinned_cat_names[$post];
					$post_category_alias = $pinned_cat_aliases[$post];
					$post_author = $pinned[$post]['author'];
				}
			}
			$post_date = explode(" ", $post_date);
			$date = $post_date[0];
			$time = $post_date[1];
			$date = explode("-", $date);
			$year = $date[0];
			$month = $date[1];
			$day = $date[2];
			$time = explode(":", $time);
			$hour = $time[0];
			$minute = $time[1];
			$second = $time[2];
			$slink = POST_SEF_LINK;
			if(preg_match("/@author@/", POST_SEF_LINK)){
				if((int) $post_author > 0 and !isset($user_login)){
					$post_author = $user->get_user_login($post_author);
				}elseif(isset($user_login)) $post_author = $user_login;
				$slink = preg_replace("/@author@/", $post_author, $slink);
			}
			$slink = preg_replace("/@alias@/", $post_alias, $slink);
			$slink = preg_replace("/@id@/", $post_id, $slink);
			$slink = preg_replace("/@title@/", $post_title, $slink);
			$slink = preg_replace("/@category@/", $post_category, $slink);
			$slink = preg_replace("/@category_alias@/", $post_category_alias, $slink);
			$slink = preg_replace("/@year@/", $year, $slink);
			$slink = preg_replace("/@month@/", $month, $slink);
			$slink = preg_replace("/@day@/", $day, $slink);
			$slink = preg_replace("/@hour@/", $hour, $slink);
			$slink = preg_replace("/@minute@/", $minute, $slink);
			$slink = preg_replace("/@second@/", $second, $slink);
			return UCMS_DIR.'/'.$slink;
		}
		else{
			return UCMS_DIR.'/?id='.get_post_id();
		}
	}
		
	function post_category_alias(){
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
			global $cat_aliases, $cat_ids, $post, $results_category_alias, $pinned_cat_aliases, $p, $pinned, $pin;
			if(isset($results_category_alias) and isset($p)){
				$post_category_alias = $results_category_alias[$p];
			}else{
				$post_category_alias = $cat_aliases[$post];
				if(isset($pinned) and empty($post_category_alias) or $pin)
					$post_category_alias = $pinned_cat_aliases[$post];
			}
			if(NICE_LINKS){
				echo UCMS_DIR."/".CATEGORY_SEF_PREFIX."/$post_category_alias";
			}else{
				echo UCMS_DIR."/?category=".$cat_ids[$post];
			}
		}
	}
		
	function post_category(){
		global $id, $udb;
		if($id != ''){
			global $id_post_query, $id_post;
			$sql = "SELECT `name` FROM `".UC_PREFIX."categories` WHERE id = '$id_post[category]'";
			$category = $udb->get_row($sql);
			echo $category['name'];
		}else{
			global $cat_names, $post, $results_category_names, $pinned_cat_names, $p, $pinned, $pin;
			if(isset($results_category_names) and isset($p)){
				$post_category = $results_category_names[$p];
			}else{
				$post_category = $cat_names[$post];
				if(isset($pinned) and empty($post_category) or $pin)
					$post_category = $pinned_cat_names[$post];
			}
			echo $post_category;
		}
	}
		
	function post_alias(){
		if(NICE_LINKS){		
			$link = explode("@", POST_SEF_LINK);
			global $aliases, $ids, $dates, $titles, $cat_names, $cat_aliases, $authors, $post, $user, $logins;
			global $results_alias, $results_date, $results_id, $results_title, $results_author, $results_category_aliases, $results_category_names, $p, $pinned, $pinned_cat_aliases, $pinned_cat_names, $pin;
			if(isset($results_alias) and isset($p)){
				$post_alias = $results_alias[$p];
				$post_id = $results_id[$p];
				$post_date = $results_date[$p];
				$post_title = $results_title[$p];
				$post_category = $results_category_names[$p];
				$post_category_alias = $results_category_aliases[$p];
				$post_author = $results_author[$p];
			}else{
				$post_alias = $aliases[$post];
				$post_id = $ids[$post];
				$post_date = $dates[$post];
				$post_title = $titles[$post];
				$post_category = $cat_names[$post];
				$post_category_alias = $cat_aliases[$post];
				for($i = 0; $i < count($logins); $i++){
					if(isset($author_id) and ($authors[$post] === $author_id)) break;
					if($authors[$post] === $logins[$i]['id']){
						$author_id = $logins[$i]['id'];
						$post_author = $authors[$post];
						$user_login = $logins[$i]['login'];
						break;
					}
				}
				if(!isset($post_author))
					$post_author = $authors[$post];
				if(isset($pinned) and empty($post_alias) or $pin){
					$post_alias = $pinned[$post]['alias'];
					$post_id = $pinned[$post]['id'];
					$post_date = $pinned[$post]['date'];
					$post_title = $pinned[$post]['title'];
					$post_category = $pinned_cat_names[$post];
					$post_category_alias = $pinned_cat_aliases[$post];
					$post_author = $pinned[$post]['author'];
				}
			}
			$post_date = explode(" ", $post_date);
			$date = $post_date[0];
			$time = $post_date[1];
			$date = explode("-", $date);
			$year = $date[0];
			$month = $date[1];
			$day = $date[2];
			$time = explode(":", $time);
			$hour = $time[0];
			$minute = $time[1];
			$second = $time[2];
			$slink = POST_SEF_LINK;
			if(preg_match("/@author@/", POST_SEF_LINK)){
				if((int) $post_author > 0 and !isset($user_login)){
					$post_author = $user->get_user_login($post_author);
				}elseif(isset($user_login)) $post_author = $user_login;
				$slink = preg_replace("/@author@/", $post_author, $slink);
			}
			$slink = preg_replace("/@alias@/", $post_alias,  $slink);
			$slink = preg_replace("/@id@/", $post_id, $slink);
			$slink = preg_replace("/@title@/", $post_title, $slink);
			$slink = preg_replace("/@category@/", $post_category, $slink);
			$slink = preg_replace("/@category_alias@/", $post_category_alias, $slink);
			$slink = preg_replace("/@year@/", $year, $slink);
			$slink = preg_replace("/@month@/", $month, $slink);
			$slink = preg_replace("/@day@/", $day, $slink);
			$slink = preg_replace("/@hour@/", $hour, $slink);
			$slink = preg_replace("/@minute@/", $minute, $slink);
			$slink = preg_replace("/@second@/", $second, $slink);
			echo UCMS_DIR.'/'.$slink;
		}
		else{
			echo UCMS_DIR.'/?id='.get_post_id();
		}
	}
		
	function post_title(){
		global $id;
		if($id != ''){
			global $id_post_query, $id_post;
			echo $id_post['title'];
			if($id_post['publish'] < 1) echo " (Черновик)";
		}else{
			global $results_title, $p, $pinned;
			if(isset($results_title) and isset($p)){
				$post_title = $results_title[$p];
			}else{
				global $titles, $post, $pin;
				$post_title = $titles[$post];
				if(isset($pinned) and empty($post_title) or $pin)
					$post_title = $pinned[$post]['title'];
			}	
			echo $post_title;
		}
	}

		
	function post_content(){
		global $id;
		if($id != ''){
			global $id_post_query, $id_post;
			//$body = preg_replace("/@-more-@(.*)-@(.*)-@/", "", $id_post['body'], 1); //ненавижу регэкспы
			if(preg_match("#(@-more-@)#", $id_post['body'])){
				$da_post = explode('@-more-@', $id_post['body'], 2);
				if(isset($da_post[1])){
					$param = explode("-@", $da_post[1], 3);
					$class = $param[0];
					$str = "$class-@$param[1]-@";
					$da_post[1] = str_replace("$str", "", $da_post[1]);
					$body = $da_post[0].$da_post[1];
					echo $body;
				}
			}else{
				echo $id_post['body'];
			}
		}else{
			global $results_body, $p, $pinned;		
			if(isset($results_body) and isset($p)){
				$post_body = $results_body[$p];
			}else{
				global $bodies, $post, $pin;
				$post_body = $bodies[$post];
				if(isset($pinned) and empty($post_body) or $pin)
					$post_body = $pinned[$post]['body'];
			}
			if(preg_match("#(<!--more-->)#", $post_body)){
				$da_post = explode('<!--more-->', $post_body);
				echo $da_post[0].'...<br><br><a href="'.get_post_alias().'" class="ncat">Читать далее</a>';
			}elseif(preg_match("#(@-more-@)#", $post_body)){
				$da_post = explode('@-more-@', $post_body);
				if(isset($da_post[1])){
					$param = explode("-@", $da_post[1], 3);
					$class = $param[0];
					echo $da_post[0].'<br><br><a name="more" href="'.get_post_alias().'" class="'.$class.'">'.$param[1].'</a>';
				}
				
			}else echo $post_body;
		}
	}
		
	function post_author($plain = false){
		global $id, $user;		
		if($id != ''){
			global $id_post_query, $id_post;
			if($plain or (int) $id_post['author'] == 0){
				echo $id_post['author'];
			}else{
				if($id_post['author'] != $user->get_user_id())
					$user_login = $user->get_user_login($id_post['author']);
				else $user_login = $user->get_user_login();
				$link = NICE_LINKS ? UCMS_DIR."/user/$user_login" : UCMS_DIR."/?action=profile&amp;id=$id_post[author]";
				echo "<a href=\"$link\">$user_login</a>";
			}
			
		}else{
			global $results_author, $p, $pinned;		
			if(isset($results_author) and isset($p)){
				$post_author = $results_author[$p];
			}else{
				global $authors, $post, $pin, $logins;
				for($i = 0; $i < count($logins); $i++){
					if(isset($author_id) and ($authors[$post] === $author_id)) break;
					if($authors[$post] === $logins[$i]['id']){
						$author_id = $logins[$i]['id'];
						$post_author = $authors[$post];
						$user_login = $logins[$i]['login'];
						break;
					}
				}
				if(!isset($post_author))
					$post_author = $authors[$post];
				if(isset($pinned) and empty($post_author) or $pin)
					$post_author = $pinned[$post]['author'];
			}		
			if($plain or (int) $post_author == 0){
				echo $post_author;
			}else{
				if(!isset($user_login)){
					if($post_author != $user->get_user_id())
						$user_login = $user->get_user_login($post_author);
					else $user_login = $user->get_user_login();
				}
				$link = NICE_LINKS ? UCMS_DIR."/user/$user_login" : UCMS_DIR."/?action=profile&amp;id=$post_author";
				echo "<a href=\"$link\">$user_login</a>";
			}
			
		}
	}

	function get_post_author(){
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
			global $results_author, $p, $pinned;		
			if(isset($results_author) and isset($p)){
				$post_author = $results_author[$p];
			}else{
				global $authors, $post, $pin;
				$post_author = $authors[$post];
				if(isset($pinned) and empty($post_author) or $pin)
					$post_author = $pinned[$post]['author'];
			}		
			if((int) $post_author == 0){
				return $post_author;
			}else{
				$user_login = $user->get_user_login($post_author);
				return $user_login;
			}
			
		}
	}
		
	function post_date(){
		global $id, $ucms;
		if($id != ''){
			global $id_post_query, $id_post;
			$date_str = $ucms->format_date($id_post['date'], false);
			echo $date_str;
		}else{
			global $results_date, $p, $pinned;		
			if(isset($results_date) and isset($p)){
				$post_date = $results_date[$p];
			}else{
				global $dates, $post, $pin;
				$post_date = $dates[$post];
				if(isset($pinned) and empty($post_date) or $pin)
					$post_date = $pinned[$post]['date'];
			}
			
			$date_str = $ucms->format_date($post_date, false);
			echo $date_str;
		}
	}
		
	function post_tags(){
		global $id, $id_post;
		if($id != ''){
			$keywords = explode(', ', $id_post['keywords']);
			if ( count($keywords) != 1 and $keywords[0] != '' ) {
				foreach ( $keywords as $keyword ) {
					$keyalias = preg_replace('/\s/', '%20', $keyword);
					$link = NICE_LINKS ? '<a href="'.UCMS_DIR.'/search/tag/'.$keyalias.'" >'.$keyword.'</a> ' : '<a href="/?action=search&amp;tag='.$keyalias.'" >'.$keyword.'</a> ';
					echo $link;
				}
			}else echo 'нет';
		}else{			
			global $results_keywords, $p, $pinned;		
			if(isset($results_keywords) and isset($p)){
				$color_tags = $results_keywords[$p];
				$post_ttags = preg_replace("#(<span style='color:black; background: yellow;'>)#", "", $results_keywords[$p]);
				$post_ttags = preg_replace("#(</span>)#", "", $post_ttags);
				if(preg_match("#(,</span> )#", $color_tags))
					$color_tags = preg_replace("#(,</span> )#", "</span>, ", $color_tags);
				$kk = explode(', ', $color_tags);
				$i = 0;
			}else{
				global $post_tags, $post, $pin;
				$post_ttags = $post_tags[$post];
				if(isset($pinned) and empty($post_ttags) or $pin)
					$post_ttags = $pinned[$post]['keywords'];
			}
			$keywords = explode(', ', $post_ttags);

			if ( count($keywords) != 1 and $keywords[0] != '' ) {
				foreach ( $keywords as $keyword )	{
					$keyalias = preg_replace('/\s/', '%20', $keyword);
					if(isset($kk) and (count($kk) != 1 and $kk[0] != '' )){
						$keyword = $kk[$i];
						$i++;
					}
					$link = NICE_LINKS ? '<a href="'.UCMS_DIR.'/search/tag/'.$keyalias.'" >'.$keyword.'</a> ' : '<a href="/?action=search&amp;tag='.$keyalias.'" >'.$keyword.'</a> ';
					echo $link;
				}
			}else echo 'нет';
		}
		
	}

	function post_comments($count, $empty, $closed){
		global $id;
		if(COMMENTS_MODULE){
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
				global $results_comment, $p, $pinned;		
				if(isset($results_comment) and isset($p)){
					$post_commentss = $results_comment[$p];
				}else{
					global $p_comments, $post, $pin;
					$post_commentss = $p_comments[$post];
					if(isset($pinned) and $post_commentss == '' or $pin)
						$post_commentss = $pinned[$post]['comments'];
				}
				if(is_comments_enabled()){
					if($post_commentss > 0):
						echo '<a href="'.get_post_alias().'#comments">'.$count.'('.$post_commentss.')</a>';
					else:
						echo '<a href="'.get_post_alias().'#comments">'.$empty.'</a>';
					endif;
				}else{
					echo $closed;
				}
				
			}
		}
	}
		
	function post_admin(){
		global $id, $user, $id_post;
		if($user->has_access(1, 2)){	
			if($id != ''){
				if($id_post['author'] == $user->get_user_id())
					$accessLVL = 2;
				elseif($user->get_user_group($id_post['author']) == 1){
					$accessLVL = 6;
				}else
					$accessLVL = 4;
				if($user->has_access(1, $accessLVL)){
					echo '<a href="'.UCMS_DIR.'/admin/posts.php?action=update&amp;id='.$id.'" >Изменить</a>';
				}
			}else{
				global $results_author, $p, $pinned;		
				if(isset($results_author) and isset($p)){
					$post_author = $results_author[$p];
					if(isset($post))
						$group = $pinned[$post]['group'];
				}else{
					global $authors, $post, $pin, $logins;
					$post_author = $authors[$post];
					for($i = 0; $i < count($logins); $i++){
						if($authors[$post] === $logins[$i]['id']){
							$group = $logins[$i]['group'];
							break;
						}
					}
					if(isset($pinned) and empty($post_author) or $pin){
						$post_author = $pinned[$post]['author'];
					}
				}
				if(!isset($group)) $group = $user->get_user_group($post_author);
				if($post_author == $user->get_user_id())
					$accessLVL = 2;
				elseif($group == 1){
					$accessLVL = 6;
				}else
					$accessLVL = 4;
				if($user->has_access(1, $accessLVL)){
					echo '<a href="'.UCMS_DIR.'/admin/posts.php?action=update&amp;id='.get_post_id().'" >Изменить</a>';
				}
	
			}
		}
	}

	function get_alias($id){
		global $udb;
		if(NICE_LINKS){
			$alias = $udb->get_row("SELECT `alias` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1");
			return $alias;
		}
	}

	function is_pinned_posts(){
		global $pinned, $pinned_count, $url_all;
		if($pinned and $pinned_count > 0){ 
			if(isset($url_all[1])){
				if($url_all[1] == 'page' or empty($url_all[1]))
					return true;
			}else
				return true;
		}
		return false;
	}

	function count_pinned(){
		global $pinned_count;
		return $pinned_count;
	}


?>