<?php
require get_module("path", $module_accessID).'manage-links.php';
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'added':
			$ucms->alert("success", "module.links.alert.success.added");
		break;

		case 'updated':
			$ucms->alert("success", "module.links.alert.success.updated");
		break;

		case 'published':
			$ucms->alert("success", "module.links.alert.success.published");
		break;

		case 'hidden':
			$ucms->alert("success", "module.links.alert.success.hidden");
		break;

		case 'deleted':
			$ucms->alert("success", "module.links.alert.success.deleted");
		break;

		case 'published_multiple':
			$ucms->alert("success", "module.links.alert.success.published_multiple");
		break;

		case 'hidden_multiple':
			$ucms->alert("success", "module.links.alert.success.hidden_multiple");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.links.alert.success.deleted_multiple");
		break;
	}
}

if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo '<h2>'.$ucms->cout("module.links.header.add", true).'</h2><br>';
			if($user->has_access("links", 2)) add_link_form();
		break;
		
		case 'update':
			echo '<h2>'.$ucms->cout("module.links.header.edit", true).'</h2><br>';
			if($user->has_access("links", 3) and $id) update_link_form($id);
		break;
		
		case 'delete':
			if($user->has_access("links", 3) and $id) delete_link($id);
		break;
	}
}else{
	echo "<h2>".$ucms->cout("module.links.header.label", true)."</h2><br>";
	if(isset($_GET['query'])){
		$ucms->cout("module.links.search.header", false, htmlspecialchars($_GET['query']));
	}
	$ucms->template(get_module('path', 'search').'forms/search-form-min.php', false);
	?>
	<a class="ucms-add-button" href="manage.php?module=links&amp;action=add"><?php $ucms->cout("module.links.add_link.button"); ?></a><br><br>
	<?php
	if(isset($_POST['add']) and $user->has_access("links", 2)):
		add_link($_POST);
	elseif(isset($_POST['update']) and $user->has_access("links", 3)):
		update_link($_POST);
	endif;
	manage_links();
}
?>
