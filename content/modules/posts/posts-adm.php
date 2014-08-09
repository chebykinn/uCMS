<?php
require get_module("path", $module_accessID).'manage-posts.php';
if(!$user->has_access("posts", 2)) header("Location: ".UCMS_DIR."/admin/index.php");
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'deleted':
			$ucms->alert("success", "module.posts.alert.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.posts.alert.deleted_multiple");
		break;

		case 'added':
			$ucms->alert("success", "module.posts.alert.added");
		break;

		case 'updated':
			$ucms->alert("success", "module.posts.alert.updated");
		break;

		case 'published':
			$ucms->alert("success", "module.posts.alert.published");
		break;

		case 'published_multiple':
			$ucms->alert("success", "module.posts.alert.published_multiple");
		break;

		case 'drafted':
			$ucms->alert("success", "module.posts.alert.drafted");
		break;

		case 'drafted_multiple':
			$ucms->alert("success", "module.posts.alert.drafted_multiple");
		break;

		case 'pinned':
			$ucms->alert("success", "module.posts.alert.pinned");
		break;

		case 'pinned_multiple':
			$ucms->alert("success", "module.posts.alert.pinned_multiple");
		break;
	}
}

if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo '<h2>'.$ucms->cout("module.posts.header.add_post.label", true).'</h2><br>';
			if($user->has_access("posts", 2)) add_post_form();
		break;

		case 'update':
			echo '<h2>'.$ucms->cout("module.posts.header.update_post.label", true).'</h2><br>';
			if($user->has_access("posts", 2) and $id) update_post_form($id);
		break;

		case 'delete':
			if($user->has_access("posts", 3) and $id) delete_post($id);
		break;

		default:
			header("Location: manage.php?module=posts");
			exit;
		break;
	}
}else{
	echo '<h2>'.$ucms->cout("module.posts.header.label", true).'</h2><br>';

	if(isset($_GET['query'])){
		$ucms->cout("module.posts.search.header", false, htmlspecialchars($_GET['query']));
	}
	$ucms->template(get_module('path', 'search').'forms/search-form-min.php', false);

	if($user->has_access("posts", 2)){ 
		?>
		<a class="ucms-add-button" href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;action=add"><?php $ucms->cout("module.posts.add_post.button"); ?></a>
		<?php 
	} 
	?>
	<br><br>
	<?php
	if(isset($_POST['add'])):
		add_post($_POST);
	elseif(isset($_POST['update'])):
		update_post($_POST);
	endif;
	manage_posts();
}
?>
