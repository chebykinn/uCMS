<?php
use uCMS\Core\Settings;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo tr('Site on Maintenance').' — '.htmlspecialchars(Settings::Get('site_title')); ?></title>
</head>
<body>
<?php
p('@s is on maintenance.', Settings::Get('site_name'));
?>
</body>
</html>
</body>
</html>