<div id="wrapper">
<a href="#sidebar" class="show-sidebar"></a>
<div id="sidebar">
	<?php
		echo $adminSidebar;
	?>
	<ul>
		<li><a href="<?php echo $homePage; ?>"><?php p('Go to site'); ?></a></li>
	</ul>
</div>

<div id="content">
<?php
	$this->region("header");
	if( $this->pageTitle() ){
		echo "<h2>".$this->pageTitle()."</h2>";
	}
	$this->showNotifications();
	include_once($adminPage);
?>
</div>
<div id="footer">
	<?php
	$this->region("footer");
	p("IVaN4B's μCMS © 2011-@s Queries: @s. Load time: @s seconds. <span class=\"ucms-version\">Version: @s</span>", 
	date('Y'), $queriesCount(),
			$loadTime(), $coreVersion); ?>
</div>
</div>