<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="utf-8">
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<?php
$this->loadStyles();
$this->loadScripts();
?>
<title><?php echo (UCMS_DEBUG ? $this->tr("[DEBUG] ") : "").htmlspecialchars($this->getTitle()); ?></title>
</head>
<body>