<?php 
if($user->has_access("posts", 4)){
	require get_module("path", $module_accessID).'manage-posts.php';
	if(isset($_GET['alert'])){
		switch ($_GET['alert']) {
			case 'added':
				$ucms->alert("success", "module.posts.categories.alert.added");
			break;
	
			case 'updated':
				$ucms->alert("success", "module.posts.categories.alert.updated");
			break;
	
			case 'updated_multiple':
				$ucms->alert("success", "module.posts.categories.alert.updated_multiple");
			break;
	
			case 'deleted':
				$ucms->alert("success", "module.posts.categories.alert.deleted");
			break;
	
			case 'deleted_multiple':
				$ucms->alert("success", "module.posts.categories.alert.deleted_multiple");
			break;
		}
	}
	if(isset($_POST['add'])):
			add_category($_POST);
		elseif(isset($_POST['update'])):
			update_category($_POST);
		endif;
		if(isset($_GET['delete'])):
			delete_category($_GET['delete']);
		endif;

	if (isset($_POST['item']) and isset($_POST['actions']) and $user->has_access("posts", 7)){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				$items[] = $id;
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					foreach ($items as $item) {
						$posts = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `category` = '$item'");
						$upd = $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = '$posts' WHERE `id` = '$item'");
					}
					
					if (count($items) > 1) {
						header("Location: ".get_current_url('update', 'alert', 'delete')."&alert=updated_multiple");
					}else 
						header("Location: ".get_current_url('update', 'alert', 'delete')."&alert=updated");
				break;
	
				case 2:
					$del = $udb->query("DELETE FROM `".UC_PREFIX."categories` WHERE `id` IN ($ids) AND `id` > '1'");
					$udb->query("UPDATE `".UC_PREFIX."posts` SET `category` = '1' WHERE `category` IN ($ids)");
					$num_posts = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `category` = '1'");
					$recount = $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = '$num_posts' WHERE `id` = '1'");
					if (count($items) > 1) {
						header("Location: ".get_current_url('update', 'alert', 'delete')."&alert=deleted_multiple");
					}else 
						header("Location: ".get_current_url('update', 'alert', 'delete')."&alert=deleted");
				break;

				case 3:
					foreach ($items as $com) {
						$com_parent = $udb->get_val("SELECT `parent` FROM `".UC_PREFIX."categories` WHERE `id` = '$com'");
						$parent_exists = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."categories` WHERE `id` = '$com_parent'");
						if(!$parent_exists){
							$udb->query("UPDATE `".UC_PREFIX."categories` SET `parent` = '0' WHERE `id` = '$com'");
						}
					}
					header("Location: ".get_current_url('alert')."&alert=updated");
				break;
				
			}
		}
	}
	$categories = $udb->get_rows("SELECT `id`, `name`, `parent` FROM `".UC_PREFIX."categories`");
	echo '<h2>'.$ucms->cout("module.posts.header.categories.label", true).'</h2><br>';
	?>
		<table>
			<tr>
				<td>
					<h3><?php $ucms->cout("module.posts.header.categories.add.label"); ?></h3>
					<form class="forms" action="manage.php?module=posts&amp;section=categories" method="post">
					<input type="hidden" name="add" value="add"><br>
					<label for="name"><b><?php $ucms->cout("module.posts.categories.form.name.label"); ?></b></label><br>
					<input type="text" name="name" style="width: 200px; height: 20px;"><br>
					<label for="alias"><b><?php $ucms->cout("module.posts.categories.form.alias.label"); ?></b></label><br>
					<input type="text" name="alias" style="width: 200px; height: 20px;"><br>
					<label for="parent"><b><?php $ucms->cout("module.posts.categories.form.parent.label"); ?></b></label><br>
					<select name="parent">
						<option value="0"><?php $ucms->cout("module.posts.categories.form.no_parent.option"); ?></option>
						<?php
						$parent = '';
						echo add_categories_tree($categories, 0, 0);
						?>
					</select><br>
					<label for="sort"><b><?php $ucms->cout("module.posts.categories.form.sort.label"); ?></b></label><br>
					<input type="number" name="sort" value="0">
					<br><br>
					<input type="submit" value="<?php echo $ucms->cout("module.posts.categories.form.add.button", true); ?>" class="ucms-button-submit">
					</form>
				</td>
				<?php if(isset($_GET['update'])){ 
					$id = (int) $_GET['update'];
					$category = $udb->get_row("SELECT * FROM `".UC_PREFIX."categories` WHERE `id` = '$id'");
					if($category){
					?>
				<td>
					<h3><?php $ucms->cout("module.posts.header.categories.update.label"); ?></h3>
					<form class="forms" action="manage.php?module=posts&amp;section=categories" method="post">
					<input type="hidden" name="id" value="<?php echo $id; ?>">
					<input type="hidden" name="update" value="update"><br>
					<label for="name"><b><?php $ucms->cout("module.posts.categories.form.name.label"); ?></b></label><br>
					<input type="text" name="name" style="width: 200px; height: 20px;" value="<?php echo $category['name']; ?>"><br>
					<label for="alias"><b><?php $ucms->cout("module.posts.categories.form.alias.label"); ?></b></label><br>
					<input type="text" name="alias" style="width: 200px; height: 20px;" value="<?php echo $category['alias']; ?>"><br>
					<label for="parent"><b><?php $ucms->cout("module.posts.categories.form.parent.label"); ?></b></label><br>
					<select name="parent">
						<option value="0"><?php $ucms->cout("module.posts.categories.form.no_parent.option"); ?></option>
						<?php
						$parent = '';
						echo add_categories_tree($categories, 0, 0, $category['parent']);
						?>
					</select><br>
					<label for="sort"><b><?php $ucms->cout("module.posts.categories.form.sort.label"); ?></b></label><br>
					<input type="number" name="sort" value="<?php echo $category['sort']; ?>">
					<br><br>
					<input type="submit" value="<?php $ucms->cout("module.posts.categories.form.edit.button"); ?>" class="ucms-button-submit">
					</form>
				</td>
				<?php 
					}
				} ?>
			</tr>
		</table>
		<?php
		
		manage_categories();
}
?>