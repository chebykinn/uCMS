<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php title(); ?></title>
</head>
<body>
<style>
* { 
	margin: 0; padding: 0; 
}

body {
	font-family: Arial, Helvetica, Georgia, Sans-serif;
	font-size: 12px;
	text-align: center; 
	color: #000000;
	background: #f8f8f8;
}

.wrapper{
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

a, a:visited{
	text-decoration: none;
	color: #1F3A6E;
}

a:hover{
	text-decoration: underline;
	color: #336699;
}

input[type=text]{
	height: 25px;
	width: 400px;
}

input[type=password]{
	height: 25px;
	width: 400px;
}

.error{
	border-radius: 2px;
	border: 1px #D93030 solid;
	width: 500px;
	background: #E6A3A3;
	color: #000;
	padding: 10px;
	margin: 0 auto;
}

.success{
	border-radius: 2px;
	border: 1px #168A24 solid;
	width: 500px;
	background: #74D96F;
	color: #000;
	padding: 10px;
	margin: 0 auto;
}

table{
	text-align: center;
}

</style>
<div class="wrapper">
<br><h2><?php $ucms->cout("template.login.header"); ?></h2><br>
<?php
$login->login_test();
echo "<br>";
$login->login_form();
?>
</div>
</body>
</html>