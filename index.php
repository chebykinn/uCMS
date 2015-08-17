<?php
require_once 'core/autoload.php';
use uCMS\Core\Loader;

Loader::GetConfiguration();

Loader::GetInstance()->init();

Loader::GetInstance()->runSite();
exit;
?>