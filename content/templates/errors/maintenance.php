<!DOCTYPE html>
<html>
<head>
<meta charset="utf8">
<title><?php $ucms->cout("template.maintenance.title"); ?></title>
</head>
<body>
<style>
* { 
	margin: 0; padding: 0; 
}

body {
	font-family: Arial, Helvetica, Georgia, Sans-serif;
	font-size: 12px;
	text-align: left;
	color: #000000;
	background: #f8f8f8;
}

a, a:visited{
	text-decoration: none;
	color: #1F3A6E;
}

a:hover{
	text-decoration: underline;
	color: #336699;
}

.maintenance{
	padding: 20px;
	border-radius: 8px;
	text-align: center;
	background: #ffffff;
	border: 1px solid #3C9CD2;
	width: 50%;
	overflow: hidden;
	margin: 0 auto;
	position: absolute;
	top: 25%;
	left: 5%;
	right: 5%;
}
</style>
<div class="maintenance">
<?php $ucms->cout("template.maintenance.message", false, SITE_NAME, UCMS_DIR); ?>
</div>
</body>
</html>