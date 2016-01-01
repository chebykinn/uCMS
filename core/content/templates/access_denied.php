<?php
use uCMS\Core\Setting;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $this->tr('Access Denied').' — '.Setting::Get('site_title'); ?></title>
</head>
<body>
<?php
$this->p('<h1>You don\'t have access to this site.</h1>');
?>
</body>
</html>
</body>
</html>