<?php
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'uCMS\\Core\\';

    // base directory for the namespace prefix
    $base_dir = dirname(__DIR__).'/core/';
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);
    $level = substr_count($relative_class, "\\");
    if( $level > 1 && strpos($relative_class, "Extensions") !== false ){
        // prevent autoload from content directory
    	return;
    }
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    // echo '<pre>';
    // var_dump($relative_class);
    // var_dump($level);
    // echo '</pre>';

    // if the file exists, require it
    if (@file_exists($file)) {
        require_once $file;
    }
});
register_shutdown_function( 'uCMS\\Core\\Debug::ErrorHandler' );
?>
