<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php $ucms->cout("ucms.template.no_iframe.title"); ?></title>
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
<div class="main"><br><h2><?php $ucms->cout("ucms.template.no_iframe.message"); ?></h2><br></div>
</body>
</html>