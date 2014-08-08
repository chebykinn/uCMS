<html>
<head>
<meta charset="utf-8">
<title>PHP or MySQL obsolete / Ваша версия PHP или MySQL устарела</title>
</head>
<body>
	<h2>Your PHP Version <?php echo PHP_VERSION; ?> or MySQL Version <?php echo $udb->mysql_version() ? $udb->mysql_version() : UCMS_MIN_MYSQL_VERSION; ?> is obsolete. 
	Minimum required: PHP <?php echo UCMS_MIN_PHP_VERSION; ?>, MySQL <?php echo UCMS_MIN_MYSQL_VERSION; ?>.</h2>
	<h2>Ваша версия PHP <?php echo PHP_VERSION; ?> или версия MySQL <?php echo $udb->mysql_version() ? $udb->mysql_version() : UCMS_MIN_MYSQL_VERSION; ?> устарела. 
	Минимально необходимые: PHP <?php echo UCMS_MIN_PHP_VERSION; ?>, MySQL <?php echo UCMS_MIN_MYSQL_VERSION; ?>.</h2>
</body>
</html>