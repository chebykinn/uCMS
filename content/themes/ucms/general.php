<?php
get_header();
?>
<div id="content">
	<?php
	varDump($this->getAction());
	$this->loadBlock($this->getAction());
	?>
</div>
<?php
get_sidebar();
get_footer();
?>