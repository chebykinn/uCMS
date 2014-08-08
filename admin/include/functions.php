<?php
function sort_by_name_asc($a, $b){
	if ($a['name'] == $b['name']) {
		return 0;
	}
	return ($a['name'] < $b['name']) ? -1 : 1;
}

function sort_by_name_desc($a, $b){
	if ($a['name'] == $b['name']) {
		return 0;
	}
	return ($a['name'] > $b['name']) ? -1 : 1;
}

function sort_by_author_asc($a, $b){
	if ($a['author'] == $b['author']) {
		return 0;
	}
	return ($a['author'] < $b['author']) ? -1 : 1;
}

function sort_by_author_desc($a, $b){
	if ($a['author'] == $b['author']) {
		return 0;
	}
	return ($a['author'] > $b['author']) ? -1 : 1;
}

function sort_by_dir_asc($a, $b){
	if ($a['dir'] == $b['dir']) {
		return 0;
	}
	return ($a['dir'] < $b['dir']) ? -1 : 1;
}

function sort_by_dir_desc($a, $b){
	if ($a['dir'] == $b['dir']) {
		return 0;
	}
	return ($a['dir'] > $b['dir']) ? -1 : 1;
}

function sort_by_date_asc($a, $b){
	if ($a['timestamp'] == $b['timestamp']) {
		return 0;
	}
	return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
}

function sort_by_date_desc($a, $b){
	if ($a['timestamp'] == $b['timestamp']) {
		return 0;
	}
	return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
}
?>