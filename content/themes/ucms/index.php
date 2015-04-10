<?php
get_header();
?>
<div id="content">
	content
	<?php
	$user = User::current();
	echo $user->getID().'<br>';
	echo $user->getName().'<br>';
	echo $user->getEmail().'<br>';
	?>
</div>
<?php
get_sidebar();
get_footer();
?>