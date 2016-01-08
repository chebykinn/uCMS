<div class="userlist">
<?php
	foreach ($users as $user) {
		?>
		<div id="<?php echo $user->uid; ?>">
			<a href="<?php echo $user->getProfilePage(); ?>" title="<?php
			echo $user->name; ?>"><?php
			echo $this->prepare($user->getDisplayName());
			?></a>
		</div>
		<?php
	}
?>
</div>