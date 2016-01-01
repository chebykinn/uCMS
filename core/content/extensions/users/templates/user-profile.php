<?php

?>
<div class="user-profile">
	<div class="user-sidebar">
	<div class="user-data">
		<?php echo $user->getDisplayName(); ?> <a href="<?php echo $logoutLink;?>" title="<?php $this->p('Logout'); ?>"><?php $this->p('Logout'); ?></a>
	</div>
	<div class="profile-menu">
	<?php
	if( $user->name == $currentUser->name ){
		$profileMenu->render();
	}

	if( $currentUser->can('manage users') ){
		?>
		<a href="<?php echo $adminEditLink; ?>" title="<?php $this->p('Edit user info'); ?>"><?php $this->p('Edit user'); ?></a>
		<?php
	}
	?>
	</div>
	</div>
	<div class="profile-content">
		<ul class="user-info">
			<?php
				if( $user->info != NULL ){
					foreach ($user->info as $field) {
						echo '<li><strong>'.$this->tr($field->data->title).':</strong> '.$field->value.'</li>';
					}
				}
			?>
		</ul>
	</div>
</div>