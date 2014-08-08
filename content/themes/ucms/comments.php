<div id="comments">
<?php if(is_comments()):?> 
	<script type="text/javascript" src="<?php theme_path(); ?>scripts/comments.js"></script>
	<?php
	if(COMMENTS_PAGING)
		pages($comments_page, $comments_count, $comments_pages_count, 10);
	if(!TREE_COMMENTS){
		for($comment = 0; $comment < comments_count(); $comment++){ ?>
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
	}else
		echo comments_tree();
	endif;
	echo '<div id="add-comment">';
		add_comment_form(); 
	echo "</div>";

	function comments_tree($root = 0, $comment = 0){
		global $user, $ucms;
		for($comment = 0; $comment < comments_count(); $comment++){
			if(get_comment_parent($comment) == $root){
				$children_menu = comments_tree(get_comment_id($comment), $comment);
				if($root != 0){
					$child = '-child';
				}else{
					$child = '';
				}
				$tree[] = '
						<div class="comment" id="comment-'.comment_id(true, $comment).'">
							<div class="combody'.$child.'">
								<div class="comavatar">
								<img src="'.comment_author_avatar(true, $comment).'" alt="" width="64" height="64">
								</div>
								<p class="comdate">'.comment_date(true, $comment).'</p>
								<p>'.comment_author(true, $comment).' <span style="color:#2f2f2f;">'.$ucms->cout('theme.ucms.comments.author.label', true).'</span></p> 
								<p>
								'.comment_content(true, $comment).'
								</p>
								'.comment_admin(true, $comment).'
								'.($user->has_access("comments", 2) ? '<p><a href="#" class="reply-to-comment">'.$ucms->cout('theme.ucms.comments.reply.label', true).'</a></p>' : '') .'
							'.$children_menu.'</div>
						</div>
				';
			}
		}
		if(isset($tree)){
			return implode('', $tree);
		}else{
			return '';
		}
}
	?>
</div>	