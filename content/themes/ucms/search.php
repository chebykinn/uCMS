<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>
<div id="content">
<?php
show_search_form();
if(is_results()):
	$ucms->cout("theme.ucms.search.query", false, get_query());
	$ucms->cout("theme.ucms.search.num_found", false, num_found());
	$link = "?action=search&amp;query=".get_query()."&amp;module=".get_searching_module().(isset($_GET['page']) ? '&amp;page='.$_GET['page'] : "");
	switch (get_searching_module()) {
		case 'posts':
			?>
			<div class="sort"><?php $ucms->cout("theme.ucms.search.sort_by.label"); ?>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=asc">↑</a>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=desc">↓</a>
			<a <?php if(get_ordering_column() == 'relevance') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>"><?php $ucms->cout("theme.ucms.search.sort_by.relevance"); ?></a>
			• <a <?php if(get_ordering_column() == 'title') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=title"><?php $ucms->cout("theme.ucms.search.sort_by.title"); ?></a>
			• <a <?php if(get_ordering_column() == 'author') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=author"><?php $ucms->cout("theme.ucms.search.sort_by.author"); ?></a>
			• <a <?php if(get_ordering_column() == 'comments') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=comments"><?php $ucms->cout("theme.ucms.search.sort_by.comments"); ?></a>
			• <a <?php if(get_ordering_column() == 'date') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=date"><?php $ucms->cout("theme.ucms.search.sort_by.date"); ?></a>
			</div>
			<?php
			for($post = 0; $post < count_results(); $post++){	
			?>
			<div class="post" id="post-<?php post_id(); ?>">
				<a href="<?php post_category_alias(); ?>" style="color:#fff;" class="cat"> <?php post_category(); ?> </a><h2><a href="<?php post_alias()?>"><?php post_title()?></a></h2>
				<div class="entry">
				<?php post_content(); ?>
					<p class="postmetadata">
						<?php $ucms->cout("theme.ucms.post.author.label");?><?php post_author(); ?>. <?php $ucms->cout("theme.ucms.post.published.label");?><?php post_date(); ?>. <?php $ucms->cout("theme.ucms.post.tags.label");?><?php post_tags(); ?> 
						<?php post_comments($ucms->cout("theme.ucms.post.comments_count.label", true), $ucms->cout("theme.ucms.post.no_comments.label", true), $ucms->cout("theme.ucms.post.comments_disabled.label", true)); ?> <?php post_admin(); ?></p>
				</div>
			</div>
			<?php 
			}
		break;

		case 'users':
			?>
			<div class="sort"><?php $ucms->cout("theme.ucms.search.sort_by.label"); ?>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=asc">↑</a>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=desc">↓</a>
			<a <?php if(get_ordering_column() == 'relevance') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>"><?php $ucms->cout("theme.ucms.search.sort_by.relevance"); ?></a>
			• <a <?php if(get_ordering_column() == 'login') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=login"><?php $ucms->cout("theme.ucms.search.sort_by.login"); ?></a>
			• <a <?php if(get_ordering_column() == 'date') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=date"><?php $ucms->cout("theme.ucms.search.sort_by.datereg"); ?></a>
			• <a <?php if(get_ordering_column() == 'lastlogin') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=lastlogin"><?php $ucms->cout("theme.ucms.search.sort_by.lastlogin"); ?></a>
			</div>
			<?php
			$user->list_users($results);
		break;

		case 'pages':
			?>
			<div class="sort"><?php $ucms->cout("theme.ucms.search.sort_by.label"); ?>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=asc">↑</a>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=desc">↓</a>
			<a <?php if(get_ordering_column() == 'relevance') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>"><?php $ucms->cout("theme.ucms.search.sort_by.relevance"); ?></a>
			• <a <?php if(get_ordering_column() == 'title') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=title"><?php $ucms->cout("theme.ucms.search.sort_by.title"); ?></a>
			• <a <?php if(get_ordering_column() == 'author') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=author"><?php $ucms->cout("theme.ucms.search.sort_by.author"); ?></a>
			• <a <?php if(get_ordering_column() == 'date') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=date"><?php $ucms->cout("theme.ucms.search.sort_by.date"); ?></a>
			• <a <?php if(get_ordering_column() == 'sort') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=sort"><?php $ucms->cout("theme.ucms.search.sort_by.page_order"); ?></a>
			</div>
			<?php
			for($p = 0; $p < count_results(); $p++){
			$alias = page_sef_links($results[$p]);	
			?>
			<div class="post" id="page-<?php echo $results[$p]['id']; ?>">
				<h2><a href="<?php echo $alias; ?>"><?php page_title($results[$p]); ?></a></h2><br>
				<div class="entry">
					<?php page_content($results[$p]); ?>
					<p><br><br><?php $ucms->cout("theme.ucms.page.author.label"); ?><?php page_author(false, $results[$p]); ?></p>
					<p style="text-align: right;"><?php page_admin($results[$p]['id'], $results[$p]); ?></p>
				</div>
			</div> 
			<?php 
			}
		break;

		case 'comments':
			?>
			<div class="sort"><?php $ucms->cout("theme.ucms.search.sort_by.label"); ?>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=asc">↑</a>
			<a href="<?php echo $link."&amp;orderby=".get_ordering_column() ?>&amp;order=desc">↓</a>
			<a <?php if(get_ordering_column() == 'relevance') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>"><?php $ucms->cout("theme.ucms.search.sort_by.relevance"); ?></a>
			• <a <?php if(get_ordering_column() == 'author') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=author"><?php $ucms->cout("theme.ucms.search.sort_by.author"); ?></a>
			• <a <?php if(get_ordering_column() == 'date') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=date"><?php $ucms->cout("theme.ucms.search.sort_by.date"); ?></a>
			• <a <?php if(get_ordering_column() == 'rating') echo 'style="font-weight: bold"'; ?> href="<?php echo $link ?>&amp;orderby=rating"><?php $ucms->cout("theme.ucms.search.sort_by.rating"); ?></a>
			</div>
			<?php
			for($comment = 0; $comment < count_results(); $comment++){ ?>
			<div class="comment" id="comment-<?php comment_id() ?>">
				
				<div class="combody">
					<div class="comavatar">
					<img src="<?php comment_author_avatar(); ?>" alt="" width="64" height="64">
					</div>
					<p class="comdate"><?php comment_date(); ?></p>
					<p><?php comment_author(); ?> <span style="color:#2f2f2f;"><?php $ucms->cout("theme.ucms.comments.author.label"); ?></span></p> 
					<p>
					<?php comment_content(); ?>
					</p>
					<?php comment_admin(); ?>
				</div>
			</div>
			<?php 
			}
		break;
	}
else:
	if(!get_query()){
		if($user->has_access("posts", 1))
			echo '<div class="warning">'.$ucms->cout("theme.ucms.search.empty_query", true).'</div>';
		else
			echo '<div class="error">'.$ucms->cout("theme.ucms.no_access.label", true).'</div>';
	}else
		echo '<div class="warning">'.$ucms->cout("theme.ucms.search.nothing_found", true).'</div>';
endif;
?>
<div class="navigation">
<?php pages($page, $count, $pages_count, 10, $sef); ?>
</div>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>