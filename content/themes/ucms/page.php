<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';
?>

<div id="content">
	<?php 
	if(page()):
		?>
		<div class="post" id="page-<?php page_id(); ?>">
			<h2><?php page_title()?></h2><br>
			<div class="entry">
				<?php page_content(); ?>
				<p><br><br>Автор: <?php page_author(); ?></p>
				<p style="text-align: right;"><?php page_admin(); ?></p>
			</div>
		</div> 
	<?php
	else:
		if($user->has_access(3, 1))
			echo '<div class="warning">Такой страницы нет.</div>';
		else
			echo '<div class="error">У вас нет доступа к этой странице.</div>';
	endif;
	?>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>
