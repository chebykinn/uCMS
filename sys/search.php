<?php 
require_once 'include/search.php';
if(file_exists(THEMEPATH.'search.php'))
	require THEMEPATH.'search.php';
else require UC_DEFAULT_THEMEPATH.'search.php';
$udb->db_disconnect($con);
?>
