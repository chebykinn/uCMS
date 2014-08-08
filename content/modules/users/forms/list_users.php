<?php
$page = $args[0];
$count = $args[1];
$pages_count = $args[2];
$users = $args[3];
pages($page, $count, $pages_count, 10);	
?>
<br><br><br> 
<table class="userlist">
	<tr>
		<?php if(USER_AVATARS) echo '<td><b>'.$ucms->cout("module.users.table.avatar.header", true).'</b></td>'; ?>
		<td><b><?php $ucms->cout("module.users.table.nickname.header"); ?></b></td>
		<td><b><?php $ucms->cout("module.users.table.group.header"); ?></b></td>
		<td><b><?php $ucms->cout("module.users.table.reg_date.header"); ?></b></td>
	</tr>
	<?php
		for($i = 0; $i < count($users); $i++){
			$alias = preg_replace("#(<span style='color:black; background: yellow;'>)#", '', trim($users[$i]['login']));
			$alias = preg_replace("#(</span>)#", '', $alias);
			$profile_link = NICE_LINKS ? UCMS_DIR."/user/".$alias : UCMS_DIR."/?action=profile&id=".$users[$i]['id'];
				?>
				<tr>
					<?php if(USER_AVATARS) echo '<td style="width:64px;"><img src="'.UCMS_DIR."/".AVATARS_PATH.$users[$i]['avatar'].'" alt="'.$users[$i]['login'].'" width="32" height="32" /></td>'; ?>
					<td style="width: 45%;"><a href="<?php echo $profile_link;?>"><?php echo (!empty($users[$i]['nickname']) ? $users[$i]['nickname'] : $users[$i]['login']);?></a>
					<?php if($users[$i]['online'] == 1){ 
							$ucms->cout("module.users.table.status.online.label");
					 	  } 
					?>
					</td>
					<td><?php echo $users[$i]['group_name']; ?></td>
					<td><?php echo $ucms->date_format($users[$i]['date'], DATE_FORMAT)?></td>
				</tr>
			<?php
		}
	

	?>
</table>