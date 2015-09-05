<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php $ucms->cout("module.comments.template.duplicate-comment.header"); ?></title>
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

.main{
	padding: 20px; 
	border-radius: 8px;
	text-align: center; 
	margin: 20% auto; 
	background: #ffffff;
	border: 1px solid #3C9CD2;
	width: 50%;
	overflow: hidden;
}
</style>
<div class="main"><br><h2><?php $ucms->cout("module.comments.template.duplicate-comment.message"); ?></h2>
	<br><br><a href="<?php echo $ucms->get_back_url(); ?>"><?php $ucms->cout("module.comments.templates.back.button"); ?></a>
</div>
</body>
</html>