<?php	
	function delete_user($id, $redirect = true){
		global $user, $udb, $event;
		if($user->get_user_id() == $id){
			$accessLVL = 3;
		}elseif($user->get_user_group($id) == 1){
			$accessLVL = 7;
		}else{
			$accessLVL = 5;
		}
		if($user->has_access("users", $accessLVL)){
			if(!empty($id)){
				$id = (int) $id;
				if($id > 1){
					$avatar = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `id` = '$id'");
					if($avatar['avatar'] != '' and $avatar['avatar'] != 'no-avatar.jpg'){
						$avatar = ABSPATH.AVATARS_PATH.$avatar['avatar'];
						unlink($avatar);
					}
					$udb->query("DELETE FROM `".UC_PREFIX."users` WHERE `id` = '$id'");
					$udb->query("DELETE FROM `".UC_PREFIX."usersinfo` WHERE `user_id` = '$id'");
					$event->do_actions("user.deleted", array($id));
					if($redirect) header("Location: ".get_current_url("action", "id", "alert")."&alert=deleted");
					return true;
				}else{
					if($redirect) header("Location: ".UCMS_DIR."/admin/manage.php?module=users");
				}
			}
		}
		return false;
	}

	function activate_user($id){
		global $user, $udb, $event;
		if($user->has_access("users", 4)){
			if(!$id)
				return false;
			else{
				$id = (int) $id;
				$udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = 1 WHERE `id` = '$id'");
				$event->do_actions("user.activated", array($id));
				header("Location: ".get_current_url("action", "id", "alert")."&alert=activated");
			}
		}
	}

	function add_user_form(){
		global $uc_months, $udb, $user, $ucms;
		$ucms->template(get_module("path", "users")."forms/add-user.php", false);
	}

	function update_user_form($id){
		global $user, $udb, $uc_months, $ucms;
		if(!$id){
			return false;	
		}else{
			$id = (int) $id;
			$data = $udb->get_row("SELECT * FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
			$profile_info = $udb->get_rows("SELECT * FROM `".UC_PREFIX."usersinfo` WHERE `user_id` = '$id'");
			$groups = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` >= '".$user->get_user_group()."' ORDER BY `id` ASC");
			if(!empty($data['id'])){
				if($data['id'] == $user->get_user_id()){
					$accessLVL = 2;
				}elseif($user->get_user_group($data['id']) == 1){
					$accessLVL = 6;
				}else{
					$accessLVL = 4;
				}
				if($user->has_access("users", $accessLVL)){ 
					$ucms->template(get_module("path", "users")."forms/update-user.php", false, $data, $groups, $id, $profile_info);
				}
			}else{
				header("Location: manage.php?module=users");
			}
		}

	}

	function add_user($p){
		global $udb, $user, $ucms, $event;
		$login = $p['login'];
		$password = $p['password'];
		$email = $udb->parse_value($p['email']);
		$group = (int) $p['group'];
		$error = false;
		if($group <= 0) $group = DEFAULT_GROUP;
		if (empty($login) or empty($password) or empty($email)) {
			$user->user_error(1);
			$error = true;
		}		
		if(!$user->check_login($login))
			$error = true;
		else
			$login = $user->check_login($login);
		
		if(!$user->check_password($password))
			$error = true;
		else
			$password = $user->check_password($password);
		
		if (!preg_match("/@/i", $email)) {
			$user->user_error(2);
			$error = true;
		}else{
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
			if(!empty($test['id']) and UNIQUE_EMAILS){
				$user->user_error(9);
				$error = true;
			}
		}
	
		if(!$error){
			if(USER_AVATARS)
				$avatar = $user->set_user_avatar($login);
			else $avatar = '';
			$ip = $user->get_user_ip();
			$result = $udb->query("INSERT INTO `".UC_PREFIX."users` (`id`, `login`, `password`, `group`, `avatar`, `email`, `activation`, `date`, `session_hash`, `regip`, `logip`, `online`, `lastlogin`)
				VALUES(NULL,'$login','$password', '$group','$avatar','$email', 1, NOW(), '', '$ip', '$ip', '0', NOW())");
			if ($result){
				$user1 = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
				$user_id = $user1['id'];

				$event->bind_action("user.add.data.add.parse", "parse_add_data");
				$event->do_actions("user.add.data.add.parse", array($user_id));
				if(!$user1){
					$user->user_error(4);
					return false;
				}
				header("Location: ".get_current_url("action", "id", "alert")."&alert=added");
			}
			else{
				$user->user_error(4);
				return false;
			}
		}else{
			return false;
		}	

	}

	function update_user($p){
		global $udb, $user, $ucms, $event;
		$user_id = (int) $p['id'];
		if(isset($p['login']) and $p['login'] != ''){
			$login = $user->check_login($p['login'], $user_id);
			if($login != ''){
				
				$udb->query("UPDATE `".UC_PREFIX."users` SET `login` = '$login' WHERE `id` = '$user_id'");
			}elseif(!$login) $error = true;
		}
		if(isset($p['password']) and $p['password'] != ''){
			$password = $user->check_password($p['password']);
			if($password != ''){
				
				$udb->query("UPDATE `".UC_PREFIX."users` SET `password` = '$password' WHERE `id` = '$user_id'");
			}
		}
		if(isset($p['group']) and $p['group'] != ''){
			$group = (int) $p['group'];
			if($group <= 0) $group = DEFAULT_GROUP;
			if($group != ''){
				
				$udb->query("UPDATE `".UC_PREFIX."users` SET `group` = '$group' WHERE `id` = '$user_id'");
			}
		}
		if(isset($p['email']) and $p['email'] != ''){
			$email = $udb->parse_value(trim(htmlspecialchars($p['email'])));
			if(!preg_match("/@/i", $email)){
				$user->user_error(2);
				$error = true;
				
			}else{
				if(UNIQUE_EMAILS){
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
					if(!empty($test['id']) and $test['id'] != $user_id){
						$user->user_error(9);
						$error = true;
					}else{
						
						$udb->query("UPDATE `".UC_PREFIX."users` SET `email` = '$email' WHERE `id` = '$user_id'");
					}
				}else{
					
					$udb->query("UPDATE `".UC_PREFIX."users` SET `email` = '$email' WHERE `id` = '$user_id'");
				}
			}
		}
		if(isset($_FILES['avatar']['name']) and $_FILES['avatar']['name'] != ''){
			if(!isset($login)) $login = $user->get_user_login($user_id);
			$avatar = $user->set_user_avatar($login);
			
			$udb->query("UPDATE `".UC_PREFIX."users` SET `avatar` = '$avatar' WHERE `id` = '$user_id'");
		}
		$event->bind_action("user.add.data.update.parse", "parse_add_data");
		$event->do_actions("user.add.data.update.parse", array($user_id));
		
		if(!isset($error)) {
			$event->do_actions("user.updated", array($user_id));
			header("Location: ".get_current_url("action", "id", "alert")."&alert=updated");
		}
	}

	function parse_add_data($user_id){
		global $udb, $user;

		if(isset($_POST['name'])){
			$values[] = $udb->parse_value((htmlspecialchars(trim($_POST['name']))));
			$columns[] = 'firstname';
		}

		if(isset($_POST['surname'])){
			$values[] = $udb->parse_value((htmlspecialchars(trim($_POST['surname']))));
			$columns[] = 'surname';
		}

		if(isset($_POST['nickname']) and ALLOW_NICKNAMES){
			$values[] = $udb->parse_value((strip_tags(trim($_POST['nickname']))));
			$columns[] = 'nickname';
		}

		if(isset($_POST['icq'])){
			$values[] = (int) $_POST['icq'];
			$columns[] = 'icq';
		}

		if(isset($_POST['skype'])){
			$values[] = $udb->parse_value($_POST['skype']);
			$columns[] = 'skype';
		}

		if(isset($_POST['day']) and $_POST['day'] != '' and isset($_POST['month']) and $_POST['month'] != '' and isset($_POST['year']) and $_POST['year'] != ''){
			$day = $_POST['day'];
			$month = $_POST['month'];
			$year = $_POST['year'];
			$values[] = $udb->parse_value("$year-$month-$day");
			$columns[] = 'birthdate';
		}

		if(isset($_POST['addinfo'])){
			$addinfo = $udb->parse_value($_POST['addinfo']);
			$values[] = strip_tags($addinfo, '<p><a><pre><img><br><b><em><i><strike>');
			$columns[] = 'addinfo';
		}

		if(isset($_POST['pm-alert']) and $_POST['pm-alert'] == 1){
			$values[] = 1;
			$columns[] = 'pm_alert';
		}else{
			$values[] = 0;
			$columns[] = 'pm_alert';	
		}
		if(!empty($columns) and !empty($values)){
			$i = 0;
			$profile_info = $udb->get_rows("SELECT * FROM `".UC_PREFIX."usersinfo` WHERE `user_id` = '$user_id'");
			foreach ($columns as $column) {
				if($user->get_user_info($column, $user_id, $profile_info) !== false){
					$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `value` = '$values[$i]' WHERE `user_id` = '$user_id' AND `name` = '$column'");
				}
				else
					$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` (`id`, `user_id`, `name`, `value`, `required`, `update`)
						VALUES(NULL, '$user_id', '$column', '$values[$i]', '0', NOW())");	
				$i++;
			}
			
		}
	}

	function manage_users(){
		global $udb, $ucms, $user;

		$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "";
		$swhere = '';
		$safe_query = '';
		$overwrite_where = '1';
		// $overwrite_selected_columns = '*';
		$s_no_include = true;
		switch ($status) {
			case 'activated':
				$swhere = "WHERE `activation` > 0";
				$overwrite_where = "`activation` > 0";
			break;

			case 'deactivated':
				$swhere = "WHERE `activation` = 0";
				$overwrite_where = "`activation` = 0";
			break;

			default:
				$swhere = "";
				$overwrite_where = "1";
			break;
		}
		$overwrite_perpage = $perpage = 25;

		include get_module("path", "search").'search.php';

		if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				$page = $udb->get_row("SELECT `group`, `id` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
				if($page){
					if(!empty($page['group'])){
						if($page['group'] == 1){
							$accessLVL = 6;
						}else{
							$accessLVL = 4;
						}
					}
				}
				if($action == 3) $accessLVL++;
				if ( $user->has_access("users", $accessLVL) and ($id != $user->get_user_id()) and ($id > 1) ) {
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						$upd = $udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = '1' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url("action", "id", "alert")."&alert=activated_multiple");
						}else {
							header("Location: ".get_current_url("action", "id", "alert")."&alert=activated");
						}
					break;
	
					case 2:
						$upd = $udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = '0' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url("action", "id", "alert")."&alert=deactivated_multiple");
						}else {
							header("Location: ".get_current_url("action", "id", "alert")."&alert=deactivated");
						}
					break;
	
					case 3:
						foreach ($items as $usr) {
							delete_user($usr, false);
						}
						if (count($items) > 1) {
							header("Location: ".get_current_url("action", "id", "alert")."&alert=deleted_multiple");
						}else {
							header("Location: ".get_current_url("action", "id", "alert")."&alert=deleted");
						}
					break;
					
				}
			}
		}

		$columns = array('login','email', 'group', 'date', 'lastlogin');
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? htmlspecialchars($_GET['orderby']) : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		if(!isset($results)){
			$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
			$cactivated = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` WHERE `activation` > 0");
			$cdeactivated = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` WHERE `activation` = 0");
			$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` $swhere ORDER BY `$orderby` $order");
			if($page <= 0) $page = 1;
			$pages_count = 0;
			if($count != 0){ 
				$pages_count = ceil($count / $perpage); 
				if ($page > $pages_count):
					$page = $pages_count;
				endif; 
				$start_pos = ($page - 1) * $perpage;
				$sql = "SELECT * FROM `".UC_PREFIX."users` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
			}
		}else{
			$call = $status == '' ? $count : $ucms->cout("module.users.search.label", true);
			$cactivated = $status == 'activated' ? $count : $ucms->cout("module.users.search.label", true);
			$cdeactivated = $status == 'deactivated' ? $count : $ucms->cout("module.users.search.label", true);
		}
		$s_link = "<a href=\"".UCMS_DIR."/admin/manage.php?module=users";

		$m_link = 
		(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['orderby']) ? "&amp;orderby=".$orderby : "")
		.(isset($_GET['order']) ? "&amp;order=".$order : "")
		.(isset($_GET['page']) ? "&amp;page=".$page : "");

		$lall = $status != '' ? 
		$s_link.$m_link."\">".$ucms->cout("module.users.show.all.link", true)."</a>" : "<b>".$ucms->cout("module.users.show.all.link", true)."</b>";

		$lactivated = $status != 'activated' ?
		$s_link."&amp;status=activated".$m_link."\">".$ucms->cout("module.users.show.activated.link", true)."</a>" : "<b>".$ucms->cout("module.users.show.activated.link", true)."</b>";

		$ldeactivated = $status != 'deactivated' ?
		$s_link."&amp;status=deactivated".$m_link."\">".$ucms->cout("module.users.show.deactivated.link", true)."</a>" : "<b>".$ucms->cout("module.users.show.deactivated.link", true)."</b>";
		if($user->has_access("users", 4)){ ?>
		<br>
		<?php echo $ucms->cout("module.users.show.label", true).$lall." ($call)"; ?> | <?php echo $lactivated." ($cactivated)"; ?> | <?php echo $ldeactivated." ($cdeactivated)"; ?>
		<br><br>
		<form action="manage.php?module=users" method="post">
		<select name="actions" style="width: 250px;">
			<option><?php $ucms->cout("module.users.selected.option"); ?></option>
			<option value="1"><?php $ucms->cout("module.users.selected.activate.option"); ?></option>
			<option value="2"><?php $ucms->cout("module.users.selected.deactivate.option"); ?></option>
			<option value="3"><?php $ucms->cout("module.users.selected.delete.option"); ?></option>
		</select>
		<input type="submit" value="<?php $ucms->cout("module.users.selected.apply.button"); ?>" class="ucms-button-submit">
		<br>
		<?php }
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}?><br>
		<table class="manage"><?php
		$link1 = UCMS_DIR."/admin/manage.php?module=users".
		(isset($_GET['query']) ? "&amp;query=".$query : "").
		(isset($_GET['status']) ? "&amp;status=".$status : "").
		"&amp;orderby=login&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link2 = UCMS_DIR."/admin/manage.php?module=users".
		(isset($_GET['query']) ? "&amp;query=".$query : "").
		(isset($_GET['status']) ? "&amp;status=".$status : "").
		"&amp;orderby=email&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link3 = UCMS_DIR."/admin/manage.php?module=users".
		(isset($_GET['query']) ? "&amp;query=".$query : "").
		(isset($_GET['status']) ? "&amp;status=".$status : "").
		"&amp;orderby=group&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link4 = UCMS_DIR."/admin/manage.php?module=users".
		(isset($_GET['query']) ? "&amp;query=".$query : "").
		(isset($_GET['status']) ? "&amp;status=".$status : "").
		"&amp;orderby=date&amp;order=".$order.(isset($_GET['page'])  ? "&amp;page=".$page : "");
		$link5 = UCMS_DIR."/admin/manage.php?module=users".
		(isset($_GET['query']) ? "&amp;query=".$query : "").
		(isset($_GET['status']) ? "&amp;status=".$status : "").
		"&amp;orderby=lastlogin&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		?>
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<?php if(USER_AVATARS){?><th><?php $ucms->cout("module.users.table.avatar.header"); ?></th><?php } ?>
				<th><a href="<?php echo $link1; ?>"><?php echo $ucms->cout("module.users.table.login.header", true).$mark; ?></a></th>
				<?php if($user->has_access("users", 4)) { ?>
				<th><a href="<?php echo $link2; ?>"><?php echo $ucms->cout("module.users.table.email.header", true).$mark; ?></a></th><?php } ?>
				<?php if($user->has_access("users", 4)) { ?>
				<th><?php $ucms->cout("module.users.table.ip.header"); ?></th>
				<th><?php $ucms->cout("module.users.table.logip.header"); ?></th><?php } ?>
				<th style="width: 80px"><a href="<?php echo $link3; ?>"><?php echo $ucms->cout("module.users.table.group.header", true).$mark; ?></a></th>
				<th><a href="<?php echo $link4; ?>"><?php echo $ucms->cout("module.users.table.reg_date.header", true).$mark; ?></a></th>
				<?php if($user->has_access("users", 4)) { ?><th><a href="<?php echo $link5; ?>"><?php echo $ucms->cout("module.users.table.login_date.header", true).$mark; ?></a></th><?php } ?>
				<th style="width: 200px"><?php $ucms->cout("module.users.table.manage.header"); ?></th>
			</tr>
			<?php
			if($count > 0){
				if(!isset($results)){
					$users = $udb->get_rows($sql);
				}else{
					$users = $results;
				}
				for ($i = 0; $i < count($users); $i++) { 
					$groups[] = $udb->parse_value($users[$i]['group']);
					$ids[] = $udb->parse_value($users[$i]['id']);
				}
	
				$groups = implode("','", $groups);
				$groups = "'".$groups."'";
				$groups_meta = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` in ($groups)");

				$ids = implode("','", $ids);
				$ids = "'".$ids."'";
				$user_infos = $udb->get_rows("SELECT `user_id`, `value` FROM `".UC_PREFIX."usersinfo` WHERE `user_id` in ($ids) AND `name` = 'nickname'");

           	 	for($i = 0; $i < count($users); $i++){
           	 		for($j = 0; $j < count($groups_meta); $j++){
						if($users[$i]['group'] === $groups_meta[$j]['id']){
							$user_group_name = $groups_meta[$j]['name'];
							break;
						}
					}
					for($j = 0; $j < count($user_infos); $j++){
						if($users[$i]['id'] === $user_infos[$j]['user_id']){
							$nickname = $user_infos[$j]['value'];
							break;
						}else{
							$nickname = "";
						}
					}
					$user_page = NICE_LINKS ? UCMS_DIR."/user/".$users[$i]['login'] : UCMS_DIR."/?action=profile&amp;id=".$users[$i]['id'];
					if($user->get_user_id() == $users[$i]['id']){
						$accessLVL = 2;
					}elseif($users[$i]['group'] == 1){
						$accessLVL = 6;
					}else{
						$accessLVL = 4;
					}
					?>
					<tr>
						<td><input type="checkbox" name="item[]" value="<?php echo $users[$i]['id']; ?>"></td>
						<?php if(USER_AVATARS){?><td style="padding: 0px; padding-top: 3px; width: 32px; text-align: center;"><img src="<?php echo "../".AVATARS_PATH.$users[$i]['avatar'] ?>" width="32" height="32" alt=""></td><?php } ?>
						<td style="width: 25%;"><a target="_blank" href="<?=$user_page?>"><?php echo $users[$i]['login'].(!empty($nickname) ? " ($nickname)" : ""); 
						if($users[$i]['id'] == $user->get_user_id()) $ucms->cout("module.users.table.status.your_profile.label"); 
						if($users[$i]['online'] == 1) $ucms->cout("module.users.table.status.online.label"); 
						if($users[$i]['activation'] != 1) $ucms->cout("module.users.table.status.deactivated.label"); ?></a></td>
						<?php if($user->has_access("users", 4)) { ?>
						<td><?php echo $users[$i]['email']; ?></td>
						<td><?php echo $users[$i]['regip']; ?></td>
						<td><?php echo $users[$i]['logip']; ?></td>
						<?php } ?>
						<td><?php echo $user_group_name; ?></td>
						<td><?php echo $ucms->date_format($users[$i]['date'])?></td>
						<?php if($user->has_access("users", 4)) { ?><td><?php echo $ucms->date_format($users[$i]['lastlogin'])?></td><?php } ?>
						<td><span class="actions"><?php if($user->has_access("users", 4)){ 
						if($users[$i]['activation'] != 1) 
							echo '<a href="'.get_current_url('action', 'id', 'alert').'&amp;action=activate&amp;id='.$users[$i]['id'].'">'
						.$ucms->cout("module.users.table.manage.activate.button", true).'</a> | ';
						} if($user->has_access("users", $accessLVL)){
						?><a href="<?php echo UCMS_DIR?>/admin/manage.php?module=users&amp;action=update&amp;id=<?=$users[$i]['id']
						?>"><?php $ucms->cout("module.users.table.manage.edit.button"); ?></a><?php }
						if($user->has_access("users", $accessLVL+1) and $users[$i]['id'] > 1){ 
						?> | <a href="<?php echo get_current_url('action', 'id', 'alert'); ?>&amp;action=delete&amp;id=<?=$users[$i]['id']
						?>"><?php $ucms->cout("module.users.table.manage.delete.button"); ?></a><?php } ?></span></td>
						
					</tr>
				<?php
           	 	}
        	}else{
        		$c = USER_AVATARS ? 10 : 9;
        		if(!$user->has_access("users", 4)) $c -= 4;
        		?>
				<td colspan="<?php echo $c; ?>" style="text-align:center;"><?php $ucms->cout("module.users.table.error.no_one_found"); ?></td>
        		<?php
        	}
		echo '</table>';
	}	
?>