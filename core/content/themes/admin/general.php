<?php
use uCMS\Core\Admin\ControlPanel;
?>
<div id="wrapper">
<a href="#sidebar" class="show-sidebar"></a>
<div id="sidebar">
	<?php
		echo ControlPanel::PrintSidebar();
	?>
	<ul>
		<li><a href="<?php echo UCMS_DIR; ?>"><?php p('Go to site'); ?></a></li>
	</ul>
</div>

<div id="content">
<?php
	$this->region("header");
	if( $this->pageTitle() ){
		echo "<h2>".$this->pageTitle()."</h2>";
	}
	$this->showNotifications();
	if($adminAction === 'home'){
		$this->region("dashboard");
	}
	ControlPanel::LoadTemplate();
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