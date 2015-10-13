<div id="block">
<div class="inner">
<?php
	if( $this->pageTitle() ){
		echo "<h2>".$this->pageTitle()."</h2>";
	}
	$loginForm->render();
?>
</div>
</div>