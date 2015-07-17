<?php
namespace uCMS\Core;
/*require_once ABSPATH.'core/ucms.php';

require_once ABSPATH.'core/debug.php';
require_once ABSPATH.'core/language/language.php';
require_once ABSPATH.'core/database/databaseConnection.php';
require_once ABSPATH.'core/database/query.php';
require_once ABSPATH.'core/cache.php';
require_once ABSPATH.'core/session.php';
require_once ABSPATH.'core/notification.php';
require_once ABSPATH.'core/tools.php';
require_once ABSPATH.'core/extensions/extension.php';
require_once ABSPATH.'core/extensions/theme.php';
require_once ABSPATH.'core/page.php';
require_once ABSPATH.'core/settings.php';
require_once ABSPATH.'core/admin/controlPanel.php';
require_once ABSPATH.'core/admin/manageTable.php';
require_once ABSPATH.'core/admin/managePage.php';
require_once ABSPATH.'core/loader.php';*/
require_once ABSPATH.'core/functions.php';
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'uCMS\\Core\\';

    // base directory for the namespace prefix
    $base_dir = ABSPATH.'core/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);
    $level = substr_count($relative_class, "\\");
    if( $level > 1 ){
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
    if (file_exists($file)) {
        require $file;
    }
});
?>