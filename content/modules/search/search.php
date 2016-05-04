<?php
$query = "";
$sef = false;
if(isset($_GET['query'])) $query = $_GET['query'];
$limit = 100;
$query = mb_substr($query, 0, $limit);
$module = DEFAULT_SEARCH_MODULE;
if(empty($module) and !in_url('admin'))
	header("Location: ".$ucms->get_back_url());
if(isset($_GET['module'])){
	$module = $_GET['module'];
}
$orderby = !empty($_GET['orderby']) ? htmlspecialchars($_GET['orderby']) : "relevance";
$order = (!empty($_GET['order']) and mb_strtolower($_GET['order'], "UTF-8") == 'asc') ? 'asc' : "desc";

$table_name = get_search_table($module, 'table');

$selected_columns = !empty($overwrite_selected_columns) ? $udb->parse_value($overwrite_selected_columns) : get_search_table($module, 'columns');

$join = get_search_table($module, 'join');

$check_where = !empty($overwrite_where) ? $udb->parse_value($overwrite_where) : get_search_table($module, 'where');

$columns_to_search_sql = get_search_table($module, 'search_in_sql') or $columns_to_search_sql = array();

$columns_to_search = get_search_table($module, 'search_in') or $columns_to_search = array();

$sort_columns = get_search_table($module, 'sort_by') or $sort_columns = array();

$columns_to_light = get_search_table($module, 'highlight') or $columns_to_light = array();

$module_file = get_search_table($module, 'file');

$perpage = !empty($overwrite_perpage) ? (int) $overwrite_perpage : RESULTS_ON_PAGE;
if(empty($check_where)){
	$check_where = '1';
}
$i = 0;
$where = "";
foreach ($columns_to_search_sql as $column) {
	$column = $udb->parse_value($column);
	$where .= "$column like '%@word@%'";
	$i++;
	if($i < count($columns_to_search_sql)){
		$where .= " OR ";
	}
}

$count = 0;
$pages_count = 0;
$results_count = 0;
$start_pos = 0;
$page = 1;

if(!$user->has_access("posts", 1)) $query = '';

if(isset($query) and $query != '' and $table_name){
	$safe_query = $udb->parse_value(htmlspecialchars($query));
	if ($safe_query != ''){
		$query = trim($safe_query);
		if(!$sef){
			if(!preg_match("/@s@/", $query))
				$keywords = explodeQuery($query);
			else $keywords[] = preg_replace("/@s@/", "", $query);
		}else $keywords[] = $query;

		if($keywords){
			foreach ($keywords as $word){
				$where = preg_replace('/@word@/i', mb_strtolower($word), $where);
				$count = $udb->num_rows("SELECT $selected_columns FROM `".UC_PREFIX."$table_name` $join WHERE ($where) AND $check_where");
				if($count > 0) break;
			}
			$count1 = $udb->num_rows("SELECT $columns_to_search_sql[0] FROM `".UC_PREFIX."$table_name` $join WHERE $check_where");
			if($count > $count1) $count = $count1;
			if($count != 0){
				if(!$sef)
					$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
				else{
					if(in_array("page", $url_all)){
						$key = array_search("page", $url_all);
						$page = $url_all[$key+1];
					}
				}
				$pages_count = ceil($count / $perpage);
				if(empty($page) or $page < 0) $page = 1;
				if($page > $pages_count) $page = $pages_count;
				$start_pos = ($page - 1) * $perpage; 
			}
			foreach ($keywords as $word){ 
				$where = preg_replace('/@word@/i', mb_strtolower($word), $where);
				$results = $udb->get_rows("SELECT $selected_columns FROM `".UC_PREFIX."$table_name` $join WHERE ($where) AND $check_where LIMIT $start_pos, $perpage");
				if($results and count($results) > 0) break;
			}
			if ($results){
				for ($i = 0; $i < count($results); $i++) {
					$results[$i]['relevance'] = 0;
					$wordWeight = 0;
					foreach ($keywords as $word) {
						$reg = "/(".preg_quote($word).")/i";
						foreach ($columns_to_search as $column) {
							$wordWeight += preg_match_all($reg, $results[$i][$column], $out);
							$wordWeight += preg_match_all($reg, $results[$i][$column], $out);
							$wordWeight += preg_match_all($reg, $results[$i][$column], $out);
						}
						
						$results[$i]['relevance'] += $wordWeight;
						if(is_array($columns_to_light) and count($columns_to_light) > 0){
							foreach ($columns_to_light as $column) {
								$results[$i][$column] = highlightWord($word, $results[$i][$column]);						
							}
						}
					}
				}
				$results_count = count($results);
			}
		}
	}
	if(isset($results) and is_array($results)){
		if(!in_array($orderby, $sort_columns)) $orderby = "relevance";
		usort($results, "sort_$order");
	}
}
if(is_file($module_file) and empty($s_no_include)){
	require $module_file;
}

function sort_desc($a, $b){
	global $orderby;
	if(!isset($orderby)) $orderby = "relevance";
	if ($a[$orderby] == $b[$orderby]) {
		return 0;
	}
	return ($a[$orderby] > $b[$orderby]) ? -1 : 1;
}

function sort_asc($a, $b){
	global $orderby;
	if(!isset($orderby)) $orderby = "relevance";
	if ($a[$orderby] == $b[$orderby]) {
		return 0;
	}
	return ($a[$orderby] < $b[$orderby]) ? -1 : 1;
}


function get_search_table($module, $column = ''){
	if(is_module($module) and in_array($module, get_modules_to_search()) or in_url('admin')){
		$file = ABSPATH.MODULES_PATH.$module.'/search.txt';
		if(file_exists($file)){
			$strings = file($file);
			if(is_array($strings) and count($strings) >= 6 and empty($column)){
				return $strings;
			}else{
				global $udb;
				switch ($column) {
					case 'table':
						$value = str_replace("table=", "", $udb->parse_value(trim($strings[0])), $count);
						if($count > 0) return $value;
					break;

					case 'columns':
						$value = str_replace("columns=", "", trim($strings[1]), $count);
						if($count > 0) return $value;
					break;

					case 'join':
						$value = str_replace("join=", "", trim($strings[2]), $count);
						if($count > 0){
							$value = str_replace("@uc_prefix@", UC_PREFIX, $value);
							return $value;
						}
					break;

					case 'where':
						$value = str_replace("where=", "", trim($strings[3]), $count);
						if($count > 0) return $value;
					break;

					case 'search_in_sql':
						$value = str_replace("search_in_sql=", "", trim($strings[4]), $count);
						if($count > 0){
							$value = explode(",", $udb->parse_value($value));
							return $value;
						}else{
							return array();
						}
					break;

					case 'search_in':
						$value = str_replace("search_in=", "", trim($strings[5]), $count);
						if($count > 0){
							$value = explode(",", $udb->parse_value($value));
							return $value;
						}else{
							return array();
						}
					break;

					case 'sort_by':
						$value = str_replace("sort_by=", "", trim($strings[6]), $count);
						if($count > 0){
							$value = explode(",", $udb->parse_value($value));
							return $value;
						}else{
							return array();
						}
					break;

					case 'highlight':
						$value = str_replace("highlight=", "", trim($strings[7]), $count);
						if($count > 0){
							$value = explode(",", $udb->parse_value($value));
							return $value;
						}else{
							return array();
						}
					break;

					case 'file':
						$value = str_replace("file=", "", trim($strings[8]), $count);
						if($count > 0){
							return ABSPATH.MODULES_PATH.$value;
						}
					break;
					
					default:
						return false;
					break;
				}
			}
		}
		return false;
	}
	return false;
}

function get_searching_module(){
	global $module;
	return $module;
}

function get_results(){
	global $results;
	if($results and count($results) > 0){
		return $results;
	}
	return false;
}

function get_query(){
	global $safe_query;
	if(!empty($safe_query))
		return $safe_query;
	else return false;
}

function num_found(){
	global $count;
	return isset($count) ? $count : 0;
		
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
	$word = preg_quote($word);
	$replacement = "<span style='color:black; background: yellow;'>".htmlspecialchars($word)."</span>";
	if(preg_match ("/^(.*)".$word."(.*).(jpg|png|gif|bmp)/", $string)){
		$result = $string;
	}else{
		$result = preg_replace("/$word/ui", $replacement, $string);
	}
	return $result;
}

function get_modules_to_search(){
	return explode(",", SEARCH_IN);
}

function get_ordering_column(){
	global $orderby;
	return $orderby;
}

function show_search_form(){
	global $ucms;
	$ucms->template(get_module("path", "search").'forms/search-form.php');
}

?>