<?php
use uCMS\Core\Loader;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php self::Translate('μCMS Error'); ?></title>
</head>
<body>
	<?php
		print '<h2>'.Loader::GetInstance()->getErrorMessage().'</h2>';
	?>
</body>
</html>
</body>
</html>