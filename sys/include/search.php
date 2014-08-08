<?php
$no_query = true;
require 'posts.php';
$query = "";
$sef = false;
if(isset($_GET['query'])) $query = $_GET['query'];
if(NICE_LINKS){
	if(in_array(TAG_SEF_PREFIX, $url_all)){ 
		$query = urldecode($url_all[3]);
		$sef = true;
	}
}else{
	if(isset($_GET['tag'])) $query = urldecode($_GET['tag']);
}
if(!$user->has_access(1, 1)) $query = '';
if(isset($query) and $query != ''){
	$safe_query = $udb->parse_value(htmlspecialchars($query));
		if ($safe_query != ''){
			$query = mb_strtolower(trim($safe_query), 'UTF-8');
			if(!$sef){
				if(!preg_match("/@s@/", $query))
					$keywords = explodeQuery($query);
				else $keywords[] = preg_replace("/@s@/", "", $query);
			}else $keywords[] = $query;
			if(!$keywords){
				$nope = true;
				$count = 0;
				$pages_count = 0;
			}else{
				$count = 0;
				foreach ($keywords as $word){ 
					$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `title` like '%$word%' OR `body` like '%$word%' OR `keywords` like '%$word%'");
					if($count > 0) break;
				}
				$count1 = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 0");
				if($count > $count1) $count = $count1;
				if($count != 0){
					if(!$sef)
						$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
					else{
						if($key = array_search("page", $url_all)){
							$page = $url_all[$key+1];
						}else $page = 1;
					}
					$pages_count = ceil($count / POSTS_ON_PAGE);
					if(empty($page) or $page < 0) $page = 1;
					if($page > $pages_count) $page = $pages_count;
					$start_pos = ($page - 1) * POSTS_ON_PAGE; 
				}else{
					$page = 1;
					$start_pos = 0;
					$pages_count = 1;
				}
				$categories = $udb->get_rows("SELECT * FROM ".UC_PREFIX."categories ORDER BY `id` DESC");
				$k = 0;
				$perpage = POSTS_ON_PAGE;
				foreach ($keywords as $word){ 
					$qposts = $udb->get_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `title` like '%$word%' OR `body` like '%$word%' OR `keywords` like '%$word%' ORDER BY `id` DESC LIMIT $start_pos, $perpage");
					if($qposts and count($qposts) > 0) break;
				}
				if ($qposts){
					for ($i = 0; $i < count($qposts); $i++) {
						$results_id[$i] = $qposts[$i]['id'];
						$results_title[$i] = $qposts[$i]['title'];
						$results_body[$i] = $qposts[$i]['body'];
						$results_keywords[$i] = $qposts[$i]['keywords'];
						$results_alias[$i] = $qposts[$i]['alias'];
						$results_author[$i] = $qposts[$i]['author'];
						$results_date[$i] = $qposts[$i]['date'];
						$results_category[$i] = $qposts[$i]['category'];
						$results_comment[$i] = $qposts[$i]['comments'];
						for($j = 0; $j < count($categories); $j++){
							if($categories[$j]['id'] == $results_category[$i]){
								$results_category_names[] = $categories[$j]['name'];
								$results_category_aliases[] = $categories[$j]['alias'];
							}
						}
						$results_relevance[$i] = 0;
						$wordWeight = 0;
						foreach ($keywords as $word) {
							$reg = "/(".$word.")/"; 
							$wordWeight += preg_match_all($reg, mb_strtolower($results_title[$i], 'UTF-8'), $out);
							$wordWeight += preg_match_all($reg, mb_strtolower($results_body[$i], 'UTF-8'), $out);
							$wordWeight += preg_match_all($reg, mb_strtolower($results_keywords[$i], 'UTF-8'), $out);
							$results_relevance[$i] += $wordWeight;
							$results_title[$i] = highlightWord($word, $results_title[$i]);
							$results_body[$i] = highlightWord($word, $results_body[$i]);
							$results_keywords[$i] = highlightWord($word, $results_keywords[$i]);
						}
					}
					$results_count = count($qposts);
				}
			}
		}else{
			$_SESSION['empty'] = true;
		}
	if(!isset($results_count)) $results_count = 0;
	for($i = 0; $i < $results_count - 1; $i++){
	    for($j = $i + 1; $j < $results_count; $j++){
	        if ($results_relevance[$i] < $results_relevance[$j]){
	            $temp = $results_relevance[$i];
	
	            $temp_id = $results_id[$i];
	            $temp_title = $results_title[$i];
	            $temp_body = $results_body[$i];
	            $temp_keywords = $results_keywords[$i];
	            $temp_alias = $results_alias[$i];
				$temp_author = $results_author[$i];
				$temp_date = $results_date[$i];
				$temp_category = $results_category[$i];
				$temp_category_names = $results_category_names[$i];
				$temp_category_aliases = $results_category_aliases[$i];
				$temp_comment = $results_comment[$i];
	
	            $results_relevance[$i] = $results_relevance[$j];
	
	            $results_id[$i] = $results_id[$j];
	            $results_title[$i] = $results_title[$j];
	            $results_body[$i] = $results_body[$j];
	            $results_keywords[$i] = $results_keywords[$j];
	            $results_alias[$i] = $results_alias[$j];
				$results_author[$i] = $results_author[$j];
				$results_date[$i] = $results_date[$j];
				$results_category[$i] = $results_category[$j];
				$results_category_names[$i] = $results_category_names[$j];
				$results_category_aliases[$i] = $results_category_aliases[$j];
				$results_comment[$i] = $results_comment[$j];
	
	            $results_relevance[$j] = $temp;
	
	            $results_id[$j] = $temp_id;
	            $results_title[$j] = $temp_title;
	            $results_body[$j] = $temp_body;
	            $results_keywords[$j] = $temp_keywords;
	            $results_alias[$j] =  $temp_alias;
				$results_author[$j] = $temp_author;
				$results_date[$j] = $temp_date;
				$results_category[$j] = $temp_category;
				$results_category_names[$j] = $temp_category_names;
				$results_category_aliases[$j] = $temp_category_aliases;
				$results_comment[$j] = $temp_comment;
	        }
	    }
	}

}
if(!isset($count)) $count = 0;
if(!isset($pages_count)) $pages_count = 0;

function get_query(){
	global $safe_query;
	if(!empty($safe_query))
		return $safe_query;
	else return false;
}

function num_found(){
	global $count;
	return $count;
		
}

function is_results(){
	global $results_count;
	if($results_count > 0){
		return true;
	}else return false;
}

function count_results(){
	global $results_count;
	return $results_count;
		
}

function dropBackWords($word) { 
	$reg = "/(ый|ой|ая|ое|ые|ому|а|о|у|е|ого|ему|и|ство|ых|ох|ия|ие|ий|ь|я|он|ют|ат)$/i"; 
	$word = preg_replace($reg,'',$word);
	return $word;
}

function stopWords($query) {
	$reg = "/\s(под|много|что|когда|где|или|которые|поэтому|все|будем|как)\s/i";
	$query = preg_replace($reg,'',$query);
	return $query;
}

function explodeQuery($query) {
	$query = rtrim($query, '/\\');
	$query = stopWords(trim($query));
	$words = explode(" ",$query);
	$i = 0;
	$keywords = "";
	foreach ($words as $word) {
		$word = trim($word);		
		if (mb_strlen($word, "utf-8") < 3) {
			unset($word);
		}
		else {
			if (mb_strlen($word, "utf-8") > 5) {
				$keywords[$i] = dropBackWords($word);
				$i++;
			}
			else {
                $keywords[$i] = $word;
                $i++;
            }
		}
	}
	return $keywords;
}

function highlightWord($word, $string) {
	$replacement = "<span style='color:black; background: yellow;'>".$word."</span>";
	if(preg_match ("/^(.*)".$word."(.*).(jpg|png|gif|bmp)/", $string)){
		$result = $string;
	}else{
		$result = str_replace($word, $replacement, $string);
	}
	return $result;
}


?>