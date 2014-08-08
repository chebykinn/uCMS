<?php 
include THEMEPATH."head.php";
include THEMEPATH."nav.php";
?>
<div id="content">
<?php
if (!$user->has_access(4, 1)){
	if(!$user->logged()){
		echo '<div class="error">';
		echo "Вход на эту страницу разрешен только зарегистрированным пользователям.";
		echo '</div>';
	}else{
		echo '<div class="error">У вас нет доступа к этой странице.</div>';
	}
	
}else{
	if(!$profile){
		echo '<div class="error">';
		echo "Такого пользователя не существует.";
		echo '</div>';
	}else{
		if(isset($post_sql)){
			echo '<a href="'.$user->get_userlist_link().'">Список пользователей</a> » <a href="'.$user->get_profile_link($user_id).'">'.$user->get_user_login($user_id).'</a> » Посты ('.$count.')<br>';
			if(is_posts()){
				for ( $post = 0; $post < posts_count(); $post++ ) { 
					?>
					<div class="post" id="post-<?php post_id(); ?>">
					<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><a href="<?php post_alias()?>"><?php post_title()?></a></h2>
					<div class="entry">
					<?php post_content(); ?>
						<p class="postmetadata">
							Автор: <?php post_author(); ?>. Опубликовано: <?php post_date(); ?>. Теги: <?php post_tags(); ?> 
							<?php post_comments('<br>Комментариев ', '<br>Нет комментариев', '<br>Комментарии отключены'); ?> <?php post_admin(); ?></p>
					</div>
					</div>
	
					<?php
				}
			}else{
				echo '<br><div class="warning">Этот пользователь еще ничего не писал.</div>';
			}
				?>
			<div class="navigation">
			<?php
			pages($page, $count, $pages_count, 10);?>
			</div>
			 <?php
				
		}elseif (isset($comments)) {
			echo '<a href="'.$user->get_userlist_link().'">Список пользователей</a> » <a href="'.$user->get_profile_link($user_id).'">'.$user->get_user_login($user_id).'</a> » Комментарии ('.$count.')<br>';
			if(is_comments()):?> 
			<?php for($comment = 0; $comment < comments_count(); $comment++){ ?>
			<div class="comment" id="comment-<?php comment_id() ?>">	
				<div class="combody">
					<div class="comavatar">
					<img src="<?php comment_author_avatar(); ?>" alt="" width="64" height="64">
					</div>
					<p class="comdate"><?php comment_date(); ?></p>
					<p><?php comment_author(); ?><span style="color:#2f2f2f;"> сказал:</span></p> 
					<p>
					<?php comment_content(); ?>
					</p>
					<?php comment_admin(); ?>
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
			echo '<br><div class="warning">Этот пользователь еще ничего не комментировал.</div>';
			endif; 
		}else{
			
			echo '<a href="'.$user->get_userlist_link().'">Список пользователей</a> » '.$profile_login.'<br>';
			$login = $user->get_user_login();
			$profile_id = $user->is_profile() ? $user->get_user_id() : $user_id;
			if($user->is_profile()){
				$pm->show_alert($user_id);	
			}		
			$pm->header_messages();
			$edit->header_messages();

			?>
			<table>
			<tr>
			<td class="user-card">
				<b><?php echo $profile_login; if($user->is_online($profile_id)){ echo " (Онлайн)"; } ?></b>
			<div>
			<img alt="аватар-<?php echo $profile_login; ?>" src="<?php echo UCMS_DIR."/".AVATARS_PATH.$profile_avatar ?>"><br><br>
			<div class="cart-group-tag"><?php echo $profile_group; ?></div>
			
			</div>
			<div><br>
			<ul class="user-menu">
			<?php if($user->is_profile()) { ?>
				<li><a title="" onclick="$('#user-info').show(); $('#user-edit').hide(); $('#user-pm').hide();" href="#">Ваш профиль</a>
				<li><a title="" onclick="$('#user-info').hide(); $('#user-edit').hide(); $('#user-pm').show();" href="#">Личные сообщения</a>
				<?php if($user->has_access(4, 2)){ ?><li><a title="" onclick="$('#user-info').hide(); $('#user-edit').show(); $('#user-pm').hide();" href="#">Редактировать информацию</a><?php }  ?>	
				<li><a href="<?php echo $user->get_logout_link(); ?>">Выход</a>
			<?php }else{ ?>
				<li><a title="" onclick="$('#user-info').show(); $('#user-pm').hide();" href="#">Профиль</a>
				<li><a title="" onclick="$('#user-info').hide(); $('#user-pm').show();" href="#">Личные сообщения</a>
				<?php if($user->has_access(4, 4)){ ?><li><a href="<?php echo UCMS_DIR; ?>/admin/users.php?action=update&amp;id=<?php echo $profile_id; ?>">Редактировать информацию</a></li><?php } ?>
			<?php } ?>
			<li><a href="<?php echo $user->get_user_contrib_link("posts", $user_id); ?>">Посты</a>
			<li><a href="<?php echo $user->get_user_contrib_link("comments", $user_id); ?>">Комментарии</a>
			</ul>
			</div>
			</td>
			<td style="vertical-align: top;">
				<div id="user-info">
					<?php
						echo "<br><br><b>Фамилия: </b>".$profile_surname;
						echo "<br><br><b>Имя: </b>".$profile_firstname;
						echo "<br><br><b>ICQ: </b>".$profile_icq;
						echo "<br><br><b>Skype: </b>".$profile_skype;
						echo "<br><br><b>Дата рождения: </b>".$ucms->format_date($profile_birthdate, false);
						echo "<br><br><b>О себе: </b>".$profile_addinfo;
					?>
				</div>
				<div id="user-pm">
					<?php 
					$pm->list_messages(); 
					$pm->send_message_form();
					?>
				</div>
			<?php 
				if($user->is_profile()) { 
					if($user->has_access(4, 2)){
						echo '<div id="user-edit">';
						$edit->edit_main_form();
						$edit->edit_add_form();
						echo '</div>';
					}
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
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';

?>
