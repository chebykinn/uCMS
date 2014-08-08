<?php 
include $theme->get_path()."head.php";
include $theme->get_path()."nav.php";
?>
<div id="content">
<?php

if (!$user->has_access("users", 1)){
	if(!$user->logged()){
		echo '<div class="error">';
		echo $ucms->cout("theme.ucms.no_access_guest.label", true);
		echo '</div>';
	}else{
		echo '<div class="error">'.$ucms->cout("theme.ucms.no_access.label", true).'</div>';
	}
	
}else{
	if(!$profile){
		echo '<div class="error">';
		echo $ucms->cout("theme.ucms.user.not_found", true);
		echo '</div>';
	}else{

		if($user_posts_page){
			echo '<a href="'.$user->get_userlist_link().'">'.$ucms->cout("theme.ucms.userlist.header", true).'</a> » <a href="'.$user->get_profile_link($user_id).'">'.$user_title_name.'</a> » '.$ucms->cout("theme.ucms.user.posts_count", true, $count).'<br>';
			if(is_posts()){
				for ( $post = 0; $post < posts_count(); $post++ ) { 
					?>
					<div class="post" id="post-<?php post_id(); ?>">
					<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><a href="<?php post_alias()?>"><?php post_title()?></a></h2>
					<div class="entry">
					<?php post_content(); ?>
						<p class="postmetadata">
							<?php $ucms->cout("theme.ucms.user.post.author.label");?><?php post_author(); ?>. <?php $ucms->cout("theme.ucms.user.post.published.label");?><?php post_date(); ?>. <?php $ucms->cout("theme.ucms.user.post.tags.label");?><?php post_tags(); ?> 
				<?php post_comments($ucms->cout("theme.ucms.user.post.comments_count.label", true), $ucms->cout("theme.ucms.user.post.no_comments.label", true), $ucms->cout("theme.ucms.user.post.comments_disabled.label", true)); ?> <?php post_admin(); ?></p>
					</div>
					</div>
	
					<?php
				}
			}else{
				echo '<br><div class="warning">'.$ucms->cout("theme.ucms.user.no_posts.label", true).'</div>';
			}
				?>
			<div class="navigation">
			<?php
			pages($page, $count, $pages_count, 10);?>
			</div>
			 <?php
				
		}elseif ($user_comments_page) {
			echo '<a href="'.$user->get_userlist_link().'">'.$ucms->cout("theme.ucms.userlist.header", true).'</a> » <a href="'.$user->get_profile_link($user_id).'">'.$user_title_name.'</a> » '.$ucms->cout("theme.ucms.user.comments_count", true, $count).'<br>';
			if(is_comments()):?> 
			<?php for($comment = 0; $comment < comments_count(); $comment++){ ?>
			<div class="comment" id="comment-<?php comment_id() ?>">	
				<div class="combody">
					<div class="comavatar">
					<img src="<?php comment_author_avatar(); ?>" alt="" width="64" height="64">
					</div>
					<p class="comdate"><?php comment_date(); ?></p>
					<p><?php comment_author(); ?><span style="color:#2f2f2f;"><?php $ucms->cout("theme.ucms.user.comments.author.label"); ?></span></p> 
					<p>
					<?php comment_content(); ?>
					</p>

					<?php 
					comment_admin();
					echo '<a href="'.UCMS_DIR.get_comment_post().'#comment-'.get_comment_id().'">'.$comments[$comment]['title'].'</a>';
					?>
				</div>
			</div>
			<?php }
				?>
			<div class="navigation">
			<?php
			pages($page, $count, $pages_count, 10);?>
			</div>
			 <?php
			else:
			echo '<br><div class="warning">'.$ucms->cout("theme.ucms.user.no_comments.label", true).'</div>';
			endif; 
		}else{
			
			echo '<a href="'.$user->get_userlist_link().'">'.$ucms->cout("theme.ucms.userlist.header", true).'</a> » '.$user_title_name.'<br>';
			$profile_id = $user->is_profile() ? $user->get_user_id() : $user_id;
			if($user->is_profile()){
				$pm->show_alert($user_id);	
			}		
			$edit->header_messages();
			$pm->header_messages();
			?>
			<table>
			<tr>
			<td class="user-card">
				<b><?php echo $user_title_name; if($user->is_online($profile_id)){ echo $ucms->cout("theme.ucms.user.online.label", true); } 
				if($user->is_profile()){ ?> <a href="<?php echo $user->get_logout_link(); ?>"><?php $ucms->cout("theme.ucms.user.logout.label");?></a><?php } ?></b>
			<div>
			<img alt="avatar-<?php echo $user_title_name; ?>" src="<?php echo UCMS_DIR."/".AVATARS_PATH.$profile_avatar ?>"><br><br>
			<div class="cart-group-tag"><?php echo $profile_group; ?></div>
			
			</div>
			<div><br>
			<ul class="user-menu">
				<li><a <?php if($user_profile_page) echo 'class="selected"'; ?> href="<?php echo $user->get_profile_link($user_id); ?>"><?php $ucms->cout("theme.ucms.user.profile.label"); ?></a></li>
				<li><a <?php if($user_messages_page) echo 'class="selected"'; ?> href="<?php echo $user->get_user_contrib_link('messages', $user_id); ?>"><?php $ucms->cout("theme.ucms.user.pm.label"); ?></a></li>
			<?php if($user->is_profile()) { ?>
				<?php if($user->has_access("users", 2)){ ?>
				<li><a <?php if($user_edit_page) echo 'class="selected"'; ?> href="<?php echo $user->get_user_contrib_link('edit', $user_id); ?>"><?php $ucms->cout("theme.ucms.user.edit_info.label"); ?></a></li><?php }  ?>	
			<?php }elseif($user->has_access("users", 4)){ ?>
				<li><a href="<?php echo $user->get_admin_edit_link($profile_id); ?>"><?php $ucms->cout("theme.ucms.user.edit_info.label"); ?></a></li>
			<?php } ?>
			<li><a href="<?php echo $user->get_user_contrib_link("posts", $user_id); ?>"><?php $ucms->cout("theme.ucms.user.posts.label"); ?></a></li></li>
			<li><a href="<?php echo $user->get_user_contrib_link("comments", $user_id); ?>"><?php $ucms->cout("theme.ucms.user.comments.label"); ?></a></li>
			</ul>
			</div>
			</td>
			<td style="vertical-align: top;">
				<?php if($user_profile_page){ ?>
				<div id="user-info">
					<?php
						echo "<br><br><b>".$ucms->cout("theme.ucms.user.profile_surname", true)."</b>".$user->get_user_info('surname', $user_id);
						echo "<br><br><b>".$ucms->cout("theme.ucms.user.profile_firstname", true)."</b>".$user->get_user_info('firstname', $user_id);
						echo "<br><br><b>".$ucms->cout("theme.ucms.user.profile_icq", true)."</b>".$user->get_user_info('icq', $user_id);
						echo "<br><br><b>".$ucms->cout("theme.ucms.user.profile_skype", true)."</b>".$user->get_user_info('skype', $user_id);
						echo "<br><br><b>".$ucms->cout("theme.ucms.user.profile_birthdate", true)."</b>".$ucms->date_format($user->get_user_info('birthdate', $user_id), DATE_FORMAT);
						echo "<br><br><b>".$ucms->cout("theme.ucms.user.profile_addinfo", true)."</b>".$user->get_user_info('addinfo', $user_id);
					?>
				</div>
				<?php 
					}elseif($user_edit_page){
						
						if($user->is_profile()) { 
							if($user->has_access("users", 2)){
								echo '<div id="user-edit">';
								$edit->edit_main_form();
								$edit->edit_add_form();
								echo '</div>';
							}
						}
					}elseif($user_messages_page){
						
						echo '<div id="user-pm">';
						$ucms->cout("theme.ucms.pm.inbox.label");
						if($pm->have_inbox_messages()){
							echo '<table class="messages">';
							for($message = 0; $message < $pm->inbox_count(); $message++){
								$author = !empty($pm->get_inbox_message("author_nickname")) ? $pm->get_inbox_message("author_nickname") : $pm->get_inbox_message("author_login");
								$author_link = NICE_LINKS ? UCMS_DIR.'/user/'.$pm->get_inbox_message("author_login") : UCMS_DIR.'/?action=profile&amp;id='.$pm->get_inbox_message("author");
								echo '<tr>';
								echo '<th>'.$ucms->cout("theme.ucms.pm.table.header.date", true).'</th>';
								echo '<th>'.$ucms->cout("theme.ucms.pm.table.header.author", true).'</th>';
								echo '<th>'.$ucms->cout("theme.ucms.pm.table.header.message", true).'</th>';
								echo '</tr>';
								echo '<tr>';
								echo '<td>'.$ucms->date_format($pm->get_inbox_message("date")).'</td>';
								echo '<td><a href="'.$author_link.'">'.$author.'</a></td>';
								echo '<td style="word-wrap: break-word; width: 200px;">'.htmlspecialchars($pm->get_inbox_message("text")).'</td>';
								echo '</tr>';
							}
							echo '</table>';
						}else{
							if($user->is_profile())
								$ucms->cout("theme.ucms.pm.inbox_empty.local.label");
							else
								$ucms->cout("theme.ucms.pm.inbox_empty.user.label");
						}
						$ucms->cout("theme.ucms.pm.outbox.label");
						if($pm->have_outbox_messages()){
							echo '<table class="messages">';
							for($message = 0; $message < $pm->outbox_count(); $message++){
								$receiver =	!empty($pm->get_outbox_message("receiver_nickname")) ? $pm->get_outbox_message("receiver_nickname") : $pm->get_outbox_message("receiver_login");
								$receiver_link = NICE_LINKS ? UCMS_DIR.'/user/'.$pm->get_outbox_message("receiver_login") : UCMS_DIR.'/?action=profile&amp;id='.$pm->get_outbox_message("receiver");
								echo '<tr>';
								echo '<th>'.$ucms->cout("theme.ucms.pm.table.header.date", true).'</th>';
								echo '<th>'.$ucms->cout("theme.ucms.pm.table.header.author", true).'</th>';
								echo '<th>'.$ucms->cout("theme.ucms.pm.table.header.message", true).'</th>';
								echo '<th></th>';
								echo '</tr>';
								echo '<tr>';
								echo '<td>'.$ucms->date_format($pm->get_outbox_message("date")).'</td>';
								echo '<td><a href="'.$receiver_link.'">'.$receiver.'</a></td>';
								echo '<td style="word-wrap: break-word; width: 200px;">'.htmlspecialchars($pm->get_outbox_message("text")).'</td>';
								echo '<td>
								<form action="" method="post">
								<input type="hidden" name="delete_message_id" value="'.$pm->get_outbox_message("id").'">
								<input class="ubutton" type="submit" value="'.$ucms->cout("theme.ucms.pm.delete.button", true).'">
								</form>
								</td>';
								echo '</tr>';
							}
							echo '</table>';
						}else{
							$ucms->cout("theme.ucms.pm.no_outbox.label");
						}
						
						//$pm->list_messages(); 
						$pm->send_message_form();
						
						echo '</div>';
					}
						?>
			</td>
			</tr>
			</table>
			<?php
		}
		
	}
}
?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';

?>
