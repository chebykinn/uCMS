<?php
get_header();
get_sidebar();
$data = get_setting('extentions');
$data = explode(',', $data);
$manage = new Management($data, 'core');
?>
<div id="content">
	<?php
		$manage->printTable();
	?>
</div>
<?php
get_footer();
?>