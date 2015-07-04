<?php
get_header();
?>
<div id="content">
	<?php
	Debug::PrintVar($this->getAction());
	$this->loadBlock($this->getAction());
	?>
</div>
<?php
get_sidebar();
get_footer();
?>