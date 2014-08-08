<?php
require get_module("path", $module_accessID)."manage-pages.php";
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'added':
			$ucms->alert("success", "module.pages.alert.success.added");
		break;

		case 'updated':
			$ucms->alert("success", "module.pages.alert.success.updated");
		break;

		case 'deleted':
			$ucms->alert("success", "module.pages.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.pages.alert.success.deleted_multiple");
		break;

		case 'published':
			$ucms->alert("success", "module.pages.alert.success.published");
		break;

		case 'published_multiple':
			$ucms->alert("success", "module.pages.alert.success.published_multiple");
		break;

		case 'drafted':
			$ucms->alert("success", "module.pages.alert.success.drafted");
		break;

		case 'drafted_multiple':
			$ucms->alert("success", "module.pages.alert.success.drafted_multiple");
		break;
		
		
	}
}

if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo '<h2>'.$ucms->cout('module.pages.header.add_page', true).'</h2><br>';
			if($user->has_access("pages", 2)) add_page_form();
		break;
		
		case 'update':
			echo '<h2>'.$ucms->cout('module.pages.header.edit_page', true).'</h2><br>';
			if($user->has_access("pages", 2) and $id) update_page_form($id);
		break;

		case 'delete':
			if($user->has_access("pages", 3) and $id) delete_page($id);
		break;
	}
}else{
	echo '<h2>'.$ucms->cout('module.pages.header', true).'</h2><br>';
	if(isset($_GET['query'])){
		$ucms->cout("module.pages.search.header", false, htmlspecialchars($_GET['query']));
	}
		
	$ucms->template(get_module('path', 'search').'forms/search-form-min.php', false);
	
	if($user->has_access("pages", 2)){ 
		?>
		<a class="ucms-add-button" href="manage.php?module=pages&amp;action=add"><?php $ucms->cout('module.pages.add_page.button'); ?></a>
		<?php 
	} 
	if(isset($_POST['add']) and $user->has_access("pages", 2)):
		add_page($_POST);
	elseif(isset($_POST['update']) and $user->has_access("pages", 2)):
		update_page($_POST);
	endif;
	?>
	<br><br>
	<?php manage_pages();
}
?>