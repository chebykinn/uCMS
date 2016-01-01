<?php
use uCMS\Core\Setting;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $this->tr('Site on Maintenance').' — '.htmlspecialchars(Setting::Get('site_title')); ?></title>
</head>
<body>
<?php
$this->p('@s is on maintenance.', Setting::Get('site_name'));
?>
</body>
</html>
</body>
</html>