<div id="comments">
<?php if(is_comments()):?> 

	<?php for($comment = 0; $comment < comments_count(); $comment++){ ?>
	<div class="comment" id="comment-<?php comment_id() ?>">
		
		<div class="combody">
			<div class="comavatar">
			<img src="<?php comment_author_avatar(); ?>" alt="" width="64" height="64">
			</div>
			<p class="comdate"><?php comment_date(); ?></p>
			<p><?php comment_author(); ?> <span style="color:#2f2f2f;"> сказал:</span></p> 
			<p>
			<?php comment_content(); ?>
			</p>
			<?php comment_admin(); ?>
		</div>
	</div>
	<?php }
	endif; 
	add_comment_form(); 
	?>
</div>	