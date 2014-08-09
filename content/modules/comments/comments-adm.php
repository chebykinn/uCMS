<?php
require get_module("path", $module_accessID)."manage-comments.php"; 
if($user->has_access("comments")){
	if(isset($_GET['alert'])){
		switch ($_GET['alert']) {
			case 'approved':
				$ucms->alert("success", "module.comments.alert.success.approved");
			break;

			case 'hidden':
				$ucms->alert("success", "module.comments.alert.success.hidden");
			break;

			case 'updated':
				$ucms->alert("success", "module.comments.alert.success.updated");
			break;

			case 'deleted':
				$ucms->alert("success", "module.comments.alert.success.deleted");
			break;

			case 'approved_multiple':
				$ucms->alert("success", "module.comments.alert.success.approved_multiple");
			break;

			case 'hidden_multiple':
				$ucms->alert("success", "module.comments.alert.success.hidden_multiple");
			break;

			case 'deleted_multiple':
				$ucms->alert("success", "module.comments.alert.success.deleted_multiple");
			break;

			case 'fixed':
				$ucms->alert("success", "module.comments.alert.success.fixed");
			break;
		}
	}
	if(isset($_GET['action'])){
		$action = $_GET['action'];
		$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
		switch ($action) {
			case 'update':
				echo '<h2>'.$ucms->cout("module.comments.header.edit", true).'</h2><br>';
				if($user->has_access("comments", 3) and $id) update_comment_form($id);
			break;

			case 'delete':
				if($user->has_access("comments", 3) and $id) delete_comment($id);
			break;

			case 'approve':
				if($user->has_access("comments", 4) and $id) approve_comment($id);
			break;

			default:
				header("Location: manage.php?module=comments");
				exit;
			break;
		}
	}else{
		if(isset($_POST['update']))
			update_comment($_POST);
		if(isset($_GET['delete']))
			delete_comment($_GET['delete']);	
		echo '<h2>'.$ucms->cout("module.comments.header", true).'</h2><br>';

		if(isset($_GET['query'])){
			$ucms->cout("module.comments.search.header", false, htmlspecialchars($_GET['query']));
		}

		$ucms->template(get_module('path', 'search').'forms/search-form-min.php', false);
		manage_comments();
	}
} ?>