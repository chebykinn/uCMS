<html>
<head>
<meta charset="utf8">
<title>Сайт на техобслуживании</title>
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
	margin: 20% auto; 
	background: #ffffff;
	border: 1px solid #3C9CD2;
	width: 50%;
	overflow: hidden;
}
</style>
<div class="maintenance"><h2>"<?php site_info("name"); ?>" на техобслуживании.</h2><br>Администрация приносит свои извинения за неудобства.
	<br><br><a href="<?php echo UCMS_DIR; ?>/admin">Вход для администраторов</a>
</div>
</body>
</html>