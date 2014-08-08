<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';
?>

<div id="content">
	<?php 
	if(is_posts()):
		if(is_pinned_posts()){
			echo "<div class=\"pinned-top\"></div><div class=\"pinned\">";
			$pin = true;
			for ($post = 0; $post < count_pinned(); $post++) { 
				?>
				<div class="post" id="post-<?php post_id(); ?>-pinned">
					<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><a href="<?php post_alias()?>"><?php post_title()?></a></h2>
					<div class="entry">
					<?php post_content(); ?>
						<p class="postmetadata">
							Автор: <?php post_author(); ?>. Опубликовано: <?php post_date(); ?>
							<a href="<?php post_alias()?>#comments"><?php post_comments('<br>Комментариев ', '<br>Нет комментариев', '<br>Комментарии отключены'); ?></a> <?php post_admin(); ?></p>
					</div>
				</div>
				<?php 
			}
			$pin = false;
			echo "</div><div class=\"pinned-bottom\"></div>";
		} 
		if(post_page()):
		?>
		<div class="post" id="post-<?php post_id(); ?>">
			<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><?php post_title()?></h2>
			<div class="entry">
			<?php post_content(); ?>
			<p class="postmetadata">
				Автор: <?php post_author(); ?>. Опубликовано: <?php post_date(); ?>. Теги: <?php post_tags(); ?> 
				<?php post_comments('<br>Комментариев ', '<br>Нет комментариев', '<br>Комментарии отключены'); ?> <?php post_admin(); ?></p>
			</div>
		</div>
		<?php list_comments(); ?>
		<?php
		else:
			for($post = 0; $post < posts_count(); $post++){	
	?>
	<div class="post" id="post-<?php post_id(); ?>">
		<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><a href="<?php post_alias()?>"><?php post_title()?></a></h2>
		<div class="entry">
		<?php post_content(); ?>
			<p class="postmetadata">
				Автор: <?php post_author(); ?>. Опубликовано: <?php post_date(); ?>
				<?php post_comments('<br>Комментариев ', '<br>Нет комментариев', '<br>Комментарии отключены'); ?> <?php post_admin(); ?></p>
		</div>
	</div>
	<?php 
			}
		?>
<div class="navigation">
	<?php pages($page, $count, $pages_count, 10); ?>
</div>
	<?php 
		endif;

	else:
		if($user->has_access(1, 1))
			echo '<div class="warning">Постов еще нет.</div>'; 
		else
			echo '<div class="error">У вас нет доступа к этой странице.</div>'; 
	endif; 
	?>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>
