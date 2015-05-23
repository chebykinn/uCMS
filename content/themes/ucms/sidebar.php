<div id="sidebar">
	<?php
		$user = User::Current();
		echo '<br>'.$user->getID().'<br>';
		echo '<br>'.$user->getName().'<br>';
		echo '<br>'.$user->getEmail().'<br>';
	?>
</div>