<?php
if( file_exists($this->getFilePath('html.php')) ){
	$this->includeFile('html.php');
}else{
	include_once(self::HTML_TEMPLATE);
}
$this->includeFile($this->themeTemplate);
?>
</body>
</html>