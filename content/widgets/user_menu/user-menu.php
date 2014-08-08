<?php
if(!$user->logged()):
	$login->login_form();
else: 
	if(isset($_SESSION['activate']) and $_SESSION['activate']){
		echo '<div class="success" style="width: 80%;">'.$ucms->cout("widget.user_menu.user_activated", true).'</div>';
		unset($_SESSION['activate']);
	}
	$profile_link = $user->get_profile_link();
	$userlist_link = $user->get_userlist_link();			
	$logout_link = $user->get_logout_link();
?>
	<div class="user-mini"> 
	<b><?php echo ($user->get_user_nickname() != "") ? $user->get_user_nickname() : $user->get_user_login(); ?></b><br>
	<?php if(USER_AVATARS) echo '<img src="'.UCMS_DIR."/".AVATARS_PATH.$user->get_user_avatar().'" alt="">'; ?>
	<ul class="umenu">
	<li class="group-tag"><?php echo $user->get_user_group_name(); ?></li>
	<li><a href="<?php echo $profile_link; ?>"><?php $ucms->cout("widget.user_menu.profile_link"); ?></a></li>
	<li><a href="<?php echo $userlist_link; ?>"><?php $ucms->cout("widget.user_menu.userlist_link"); ?></a></li>
	<li><a href="<?php echo $logout_link; ?>"><?php $ucms->cout("widget.user_menu.logout_link"); ?></a><br></li>
	<?php if($user->has_access("at_least_one", 4)) echo '<li><a href="'.UCMS_DIR.'/admin">'.$ucms->cout("widget.user_menu.admin_panel_link", true).'</a><br></li>'; ?>
	</ul>
	</div> 
<?php
endif; 
?>