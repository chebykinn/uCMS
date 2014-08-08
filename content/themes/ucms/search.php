<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';
?>

<div id="content">
<?php
if(is_results()):
	echo '<br><h3>Поиск - "'.get_query().'"</h3><br>';
	echo "<br><b>Найдено: </b>".num_found();
		for($p = 0; $p < count_results(); $p++){	
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
else:
	if(!get_query()){
		if($user->has_access(1, 1))
			echo '<div class="warning">Задан пустой поисковый запрос!</div>';
		else
			echo '<div class="error">У вас нет доступа к этой странице.</div>';
	}else
		echo '<div class="warning">Ничего не нашлось...</div>';
endif;
?>
<div class="navigation">
<?php pages($page, $count, $pages_count, 10, $sef); ?>
</div>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>