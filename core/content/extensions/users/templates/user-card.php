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
* $loginForm - login form object
* 
*/
if( !$user->isLoggedIn() ):
	$loginForm->render();
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
	<?php echo $user->group->name; ?>
	</div>
	<?php
		if( $userMenu != NULL ){
			$userMenu->render('user-menu');
		}
	?>
	</div> 
<?php
endif; 
?>