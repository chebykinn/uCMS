<?php
$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
$perpage = 20;
if(NICE_LINKS){
	$action2 = isset($url_all[2]) ? $url_all[2] : '';
	if(!empty($action2)){
		if($action2 == 'page'){
			$page = isset($url_all[3]) ? (int) $url_all[3] : 1;
			if($page <= 0) $page = 1;
		}
	}else{
		$page = 1;
	}
}else{
	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
}
if($page <= 0) $page = 1;
	if($count != 0){ 
		$pages_count = ceil($count / $perpage); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * $perpage;
		$users = $udb->get_rows("SELECT * FROM `".UC_PREFIX."users` ORDER BY `id` DESC LIMIT $start_pos, $perpage");
	}
if(file_exists(THEMEPATH.'userlist.php'))
	require_once THEMEPATH.'userlist.php';
else require UC_DEFAULT_THEMEPATH.'userlist.php';
$udb->db_disconnect($con);
?>
