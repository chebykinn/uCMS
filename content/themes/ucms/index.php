<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>
<div id="content">
	<?php 
	switch ($action) {
		case 'archive':
			if(isset($uc_months[$month]))
				$ucms->cout("theme.ucms.post.archives.title", false, $uc_months[$month], $year);
		break;

		case 'tag':
			$ucms->cout("theme.ucms.post.tag.title", false, $tag);
		break;

		case 'category':
			$ucms->cout("theme.ucms.post.category.title", false, $category_name);
		break;
	}
	if(is_posts()):
		if(post_page()):
		?>
		<div class="post" id="post-<?php post_id(); ?>">
			<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><?php post_title()?></h2>
			<div class="entry">
			<?php post_content(); ?>
			<p class="postmetadata">
				<?php $ucms->cout("theme.ucms.post.author.label"); post_author(); ?>. <?php $ucms->cout("theme.ucms.post.published.label");
				echo '<time datetime="'.get_post_date().'">'; post_date(); echo '</time>'; 
				?>. <?php $ucms->cout("theme.ucms.post.tags.label"); post_tags(); ?> 
				<br><?php post_comments(
				$ucms->cout("theme.ucms.post.comments_count.label", true),
				$ucms->cout("theme.ucms.post.no_comments.label", true),
				$ucms->cout("theme.ucms.post.comments_disabled.label", true)); ?>&nbsp;<?php post_admin(); ?></p>
			</div>
		</div>
		<?php list_comments();
		else:
			if(is_pinned_posts()){
				echo "<div class=\"pinned-top\"></div><div class=\"pinned\">";
				pinned_posts_started();
			}
			for($post = 0; $post < posts_count(); $post++){
				if( pinned_posts_ended() ){
					echo "</div><div class=\"pinned-bottom\"></div>";
				} 
				
	?>
	<div class="post" id="<?php if( listing_pinned_post() ) echo "pinned-"; ?>post-<?php post_id(); ?>">
		<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><a href="<?php post_alias()?>"><?php post_title()?></a></h2>
		<div class="entry">
		<?php post_content(); ?>
			<p class="postmetadata">
				<?php $ucms->cout("theme.ucms.post.author.label"); ?><?php post_author(); ?>. <?php $ucms->cout("theme.ucms.post.published.label"); 
				echo '<time datetime="'.get_post_date().'">'; post_date(); echo '</time>'; ?>
				<br><?php post_comments(
				 $ucms->cout("theme.ucms.post.comments_count.label", true),
				 $ucms->cout("theme.ucms.post.no_comments.label", true),
				 $ucms->cout("theme.ucms.post.comments_disabled.label", true)); ?>&nbsp;<?php post_admin(); ?></p>
		</div>
	</div>
	<?php
				pinned_post();
			}
		?>
<div class="navigation">
	<?php 
	pages($page, $count, $pages_count, 10); ?>
</div>
	<?php 
		endif;

	else:
		if($user->has_access("posts", 1))
			echo '<div class="warning">'.$ucms->cout("theme.ucms.no_posts.label", true).'</div>'; 
		else
			echo '<div class="error">'.$ucms->cout("theme.ucms.no_access.label", true).'</div>'; 
	endif; 
	?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>
