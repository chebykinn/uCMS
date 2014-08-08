<?php
function pages($page, $count, $pages_count, $show_link, $sef = NICE_LINKS){
	global $id;
	if ($pages_count == 1) return false;
	$link = SITE_DOMAIN.$_SERVER['REQUEST_URI'];
	if($sef){
		global $url_all;
		if(in_array("page", $url_all)){	
			$link = preg_replace("#(/page/(\d+))#", "", $link);
		}
		if(substr($link, -1) != "/")
			$alias = "/page/";
		else $alias = "page/";
	}else{
		if(isset($_GET['page'])){
			$link = preg_replace("#(page=(\d+))#", "", $link);
		}elseif(!empty($_GET)) $link .= '&amp;';
		else $link .= '?';
		$alias = 'page=';
	}
	$separator = ' ';
	$style = 'class="pages"';
	$begin = $page - intval($show_link / 2);
	unset($show_dots);
	if ($pages_count <= $show_link + 1) $show_dots = 'no';
	if ($begin > 2 && ($pages_count - $show_link > 2)){
		echo '<a '.$style.' style="margin-right: 4px" href="'.$link.$alias.'1"> ← </a>';
		echo $separator;
	}
	for ($j = 0; $j <= $show_link; $j++){
		$i = $begin + $j;
		if ($i < 1) continue;
		if (!isset($show_dots) && $begin > 1) {
			echo '<a '.$style.' href="'.$link.$alias.($i-1).'"><b>...</b></a>';
			$show_dots = "no";
			echo $separator;
		}
		if ($i > $pages_count) break;
		if ($i == $page) {
			$style = 'class="pages-selected"';
			echo '<a '.$style.'><b>'.$i.'</b></a>';
			$style = 'class="pages"';
		} else {
			echo '<a '.$style.' href="'.$link.$alias.$i.'">'.$i.'</a>'; 
		}
		if (($i != $pages_count) && ($j != $show_link)) echo $separator;
		if (($j == $show_link) && ($i < $pages_count)) {
			echo $separator;
			echo '<a '.$style.' href="'.$link.$alias.($i+1).'"><b>...</b></a>';
			echo $separator;
		}
	}
	if ($begin + $show_link + 1 < $pages_count) {
		echo '<a '.$style.' href="'.$link.$alias.$pages_count.'"> → </a>';	
	}
	return true;
}

function pages_min($page, $count, $pages_count, $show_link, $to_left, $to_right, $to_top = '', $to_bottom = ''){
	global $id;
	if($id != 0){
		return false;
	}else{
		
		if($to_bottom != '' and $page != $pages_count){
			echo "<a href=\"?page=$pages_count\">$to_left</a> ";
		}

		if($page != $pages_count){
			echo "<a href=\"?page=".($page+1)."\">$to_left</a> ";
		}
		if($page > 1){
			echo "&nbsp;&nbsp;<a href=\"?page=".($page-1)."\">$to_right</a>";
		}

		if($to_top != '' and $page > 1){
			echo "<a href=\"?page=1\">$to_left</a> ";
		}
	}
	
}
?>