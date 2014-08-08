<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">	
<link rel="stylesheet" href="<?php theme_path(); ?>style.css" type="text/css" media="screen">
<?php rss_link(); ?>
<script type='text/javascript' src='<?php echo UCMS_DIR; ?>/sys/include/jquery.js'></script>
<link rel="apple-touch-icon" href="<?php echo UCMS_DIR; ?>/favicon.ico">
<title>
<?php title(); ?>
</title>
</head>
<body>
<div id="wrapper">
<div id="head">
<h1><a href="<?php echo UCMS_DIR; ?>/"><?php site_info('name'); ?></a></h1><br><br>
<h2><?php site_info('description'); ?></h2>
</div>
