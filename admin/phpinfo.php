<?php
$title = "Информация о PHP :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(5, 7)) header("Location: index.php");
?><div id="content">
<style type="text/css">
#phpinfo {

}

#phpinfo pre {

}

#phpinfo a:link {

}

#phpinfo a:hover {

}

#phpinfo table {
	width: 100%;
	background: #ccc;
	border-radius: 4px;
}

#phpinfo .center {
	background: #fff;
	border-radius: 4px;
	border: 2px solid #ccc;
	padding: 10px;
	border-spacing: 10px;
	margin: 10px;
}

#phpinfo .center table {
	
}

#phpinfo .center th {
	
}

#phpinfo .center td{
	
}

#phpinfo th {
	padding: 5px;
	text-align: left;
	background: #E3E3E3;
	border-radius: 2px;
}

#phpinfo td{
 	padding: 5px;
	text-align: left;
	background: #fff;
	border-radius: 2px;
}

#phpinfo h1 {

}

#phpinfo h2 {

}

#phpinfo .p {

}

#phpinfo .e {
	width: 20%;
	font-weight: bold;
}

#phpinfo .h {

}

#phpinfo .v {

}

#phpinfo .vr {

}

#phpinfo img {

}

#phpinfo hr {

}
 </style>
<div id="phpinfo">
<?php

ob_start();
phpinfo();
$pinfo = ob_get_contents();
ob_end_clean();
echo str_replace ( "module_Zend Optimizer", "module_Zend_Optimizer", preg_replace ( '%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo ) );

?>
</div>
<?php include "footer.php"; ?>