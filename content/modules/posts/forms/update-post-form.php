<?php
include ABSPATH.'/admin/text-editor.php';
$editor = new editor();
$post = $args[0];
$category = $args[1];
?>
	<form method="post" action="manage.php?module=posts">
		<input type="hidden" name="update" value="true" >
		<input type="hidden" name="id" value="<?=$post['id']?>" >
		<input type="hidden" name="author" value="<?=$post['author']?>" >
		<?php $editor->text_input(htmlspecialchars(stripcslashes($post['body']))); ?>
		<table class="forms" style="width:100%">
			<tr>
				<td width="80px"><label for="title"><?php $ucms->cout("module.posts.form.title.label"); ?></label></td>
				<td><input type="text" name="title" id="title" value="<?=$post['title']?>"></td> 
			</tr>
			<tr>
				<td width="80px"><label for="alias"><?php $ucms->cout("module.posts.form.alias.label"); ?></label></td>
				<td><input type="text" name="alias" id="alias" value="<?=$post['alias']?>"></td> 
			</tr>
			<tr>
				<td></td>
				<td><?php $editor->controls(); ?></td>
			</tr>
			<tr>
				<td><label for="editor"><?php $ucms->cout("module.posts.form.editor.label"); ?></label></td> 
				<td><?php $editor->main(); ?></td>
			<tr>		
				<td><label for="keywords"><?php $ucms->cout("module.posts.form.keywords.label"); ?></label></td>
				<td><input type="text" name="keywords" id="keywords" value="<?=$post['keywords']?>"></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.posts.form.category.label"); ?></td>
				<td><select name="category" size="1">
				<?php
					echo '<option selected value="'.$post['category'].'">'.$category['name'].'</option>';
					$catlist = "SELECT * FROM `".UC_PREFIX."categories`";
					$cats = $udb->get_rows($catlist);
					for($i = 0; $i < count($cats); $i++){
						echo '<option value="'.$cats[$i]['id'].'">'.$cats[$i]['name'].'</option>';
					}
					?>
				</select>
				</td>
			</tr>
			<?php if($user->has_access("posts", 4)){ ?>
			<tr>
				<td><?php $ucms->cout("module.posts.form.author.label"); ?></td>
				<td><input type="text" value="<?php 
					if((int) $post['author'] == 0)
						echo $post['author'];
					else{
						echo $user->get_user_login($post['author']);
					} 
				?>" id="author" name="author"></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.posts.form.date.label"); ?></td>
				<td>
					<?php
					$date = explode(" ", $post['date']);
					$time = explode(":", $date[1]);
					$date = explode("-", $date[0]);
					?>
					<input type="number" name="day" style="width:50px;" min="1" max="31" placeholder="<?php $ucms->cout("module.posts.form.day.placeholder"); ?>" value="<?php echo $date[2] ?>">
					<select name="month" style="width:100px;">
						<?php
						echo "<option value=".$date[1].">".$uc_months[$date[1]]."</option>";
						echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
						for ($i = 1; $i <= 12; $i++) {
							$m = $i < 10 ? "0$i" : $i;
							echo "<option value=\"$m\">$uc_months[$m]</option>";
						}
						?>
					</select>
					<input type="number" name="year" min="1900" max="<?php echo date("Y") ?>" style="width:50px;" placeholder="<?php $ucms->cout("module.posts.form.year.placeholder"); ?>" value="<?php echo $date[0] ?>">
					<?php $ucms->cout("module.posts.form.time.label"); ?>
					<input type="text" name="hour" style="width: 15px; height: 15px;" value="<?php echo $time[0]; ?>"> :
					<input type="text" name="minute" style="width: 15px; height: 15px;" value="<?php echo $time[1]; ?>"> :
					<input type="text" name="second" style="width: 15px; height: 15px;" value="<?php echo $time[2]; ?>">
				</td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.posts.form.pin.label"); ?></td>
				<td><input type="checkbox" value="2" id="pin" name="pin" <?php if($post['publish'] == 2) echo "checked"; ?>></td>
			</tr>
			<?php } ?>
			<tr>
				<td><?php $ucms->cout("module.posts.form.publish.label"); ?></td>
				<td><input type="checkbox" value="1" id="publish" name="publish" <?php if($post['publish'] > 0) echo "checked"; ?>></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.posts.form.comment.label"); ?></td>
				<td><input type="checkbox" value="-1" id="comment" name="comment" <?php if($post['comments'] < 0) echo "checked"; ?>></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="submit" class="ucms-button-submit" onclick="makePublish()" value="<?php $ucms->cout("module.posts.form.update.button"); ?>" /></td>
			</tr>
		</table>
	</form>
