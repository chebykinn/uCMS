<div id="header">
	<?php
		$this->printRegionBlocks('header');
	?>
</div>
<div id="wrapper">
<div id="content">
	<?php
	Debug::PrintVar(Page::GetCurrent()->getAction());
	if( $this->getErrorCode() === 404 ){
		echo "EГГОГ 404";
	}else{
		//$this->loadBlock(Page::GetCurrent()->getAction());		
	}
	$this->printRegionBlocks('content');
	?>
</div>
<div id="sidebar">
	<?php

	$this->printRegionBlocks('right-sidebar');
	$user = User::Current();
	echo '<br>'.$user->getID().'<br>';
	echo '<br>'.$user->getName().'<br>';
	echo '<br>'.$user->getEmail().'<br>';
	?>
</div>
</div>
<div id="footer">
	<?php
	$this->printRegionBlocks('footer');
	echo '<br>'.DatabaseConnection::GetDefault()->getQueriesCount();
	echo '<br>'.uCMS::getInstance()->getLoadTime();
	?>
</div>