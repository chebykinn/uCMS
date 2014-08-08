<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>

<div id="content">
	<?php 
	if(page()):
		?>
		<div class="post" id="page-<?php page_id(); ?>">
			<h2><?php page_title()?></h2><br>
			<div class="entry">
				<?php page_content(); ?>
				<p><br><br><?php 
				$ucms->cout("theme.ucms.page.author.label"); 
				page_author(); 
				$ucms->cout("theme.ucms.page.date.label"); 
				page_date(); 
				?></p>
				<p style="text-align: right;"><?php page_admin(); ?></p>
			</div>
		</div> 
	<?php
	else:
		if($user->has_access(3, 1))
			echo '<div class="warning">'.$ucms->cout("theme.ucms.no_page.label", true).'</div>';
		else
			echo '<div class="error">'.$ucms->cout("theme.ucms.no_access.label", true).'</div>';
	endif;
	?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>
