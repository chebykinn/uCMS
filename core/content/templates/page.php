<?php
if( file_exists($this->getFilePath('html.php')) ){
	include_once($this->getFilePath('html.php'));
}else{
	include_once(self::HTML_TEMPLATE);
}
include_once($this->getFilePath($this->themeTemplate));
?>
</body>
</html>