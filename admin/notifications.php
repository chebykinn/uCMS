<?php 
include "head.php"; 
include "sidebar.php";
if(!$user->has_access(5, 7)) header("Location: index.php");
echo '<div id="content">';
if(isset($_SESSION['success'])){
	echo '<div class="success">Email\'ы успешно изменены.</div>';
	unset($_SESSION['success']);
}

if(isset($_POST['edit']) and $user->has_access(5, 7)){
	$admin_email = $udb->parse_value($_POST['admin_email']);
	$comment_email = $udb->parse_value($_POST['comment_email']);
	$new_user_email = $udb->parse_value($_POST['new_user_email']);
	$upd1 = $udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$admin_email' WHERE `id` = '19'");
	$upd2 = $udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$comment_email' WHERE `id` = '20'");
	$upd3 = $udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$new_user_email' WHERE `id` = '21'");
	if($upd1 and $upd2 and $upd3){
		$_SESSION['success'] = true;
		header("Location: notifications.php");
	}
}		
?>
<h2>Уведомления</h2><br>
<form class="forms" style="width: 800px;" action="notifications.php" method="post">
	<table style="width: 800px;">
		<tr>
			<td>Email для обратной связи и общих уведомлений:</td>
			<td><input name="admin_email" type="text" value="<?php echo ADMIN_EMAIL; ?>"></td>
		</tr>
		<tr>
			<td>Email для уведомления о добавлении нового комментария:</td>
			<td><input name="comment_email" type="text" value="<?php echo COMMENTS_EMAIL; ?>"></td>
		</tr>
		<tr>
			<td>Email для уведомления о регистрации нового пользователя:</td>
			<td><input name="new_user_email" type="text" value="<?php echo NEW_USER_EMAIL; ?>"></td>
		</tr>
		<tr>
			<td><input name="edit" class="ucms-button-submit" type="submit" value="Изменить"></td>
		</tr>
	</table>
</form>
<?php include "footer.php"; ?>