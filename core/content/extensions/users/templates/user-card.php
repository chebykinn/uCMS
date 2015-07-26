<?php
/**
* Example block
* 
* List of provided variables:
* $user - current user object
* $profileLink - link to user profile
* $userlistLink - link to users list
* $logoutLink - link to logout
* $cpanelLink - link to control panel
* 
*/
if( !$user->isLoggedIn() ):
	echo 'login form';
	//$login->login_form();
else:
?>
	<div class="user-card"> 
	<b><?php echo $user->getDisplayName(); ?></b><br>
	<?php 
	if( $user->getAvatar() ){
		echo "<img src=\"{$user->getAvatar()}\" alt=\"\">";
	}
	?>
	<div class="group-tag">
	<?php echo $user->getGroup()->getName(); ?>
	</div>
	<ul class="user-menu">
	<li><a href="<?php echo $profileLink; ?>"><?php p("Profile"); ?></a></li>
	<li><a href="<?php echo $userlistLink; ?>"><?php p("Users") ?></a></li>
	<li><a href="<?php echo $logoutLink; ?>"><?php p("Logout"); ?></a></li>
	<?php if( $user->can("access control panel") ): ?>
	<li><a href="<?php echo $cpanelLink; ?>"><?php p("Control Panel"); ?></a></li>
	<?php endif; ?>
	</ul>
	</div> 
<?php
endif; 
?>