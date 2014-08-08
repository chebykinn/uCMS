<?php 
require_once 'search.php';
if(get_query() != ''){
	add_title( $action, 'module.search.site.title', array( htmlspecialchars( get_query() ) ) );
}else{
	add_title( $action, 'module.search.site.title.empty');
}
if(file_exists($theme->get_path().'search.php'))
	require $theme->get_path().'search.php';
else require UC_DEFAULT_THEMEPATH.'search.php';

?>