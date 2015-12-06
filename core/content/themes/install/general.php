<div class="wrapper">
<div class="inner">
<?php
use uCMS\Core\Installer;
use uCMS\Core\Notification;
if( !Installer::IsRunning() ){
	$error = new Notification(tr('Error: Attempted to use install theme as site theme!'), Notification::ERROR);
	$error->add();
	$this->showNotifications();
}else{
	$this->showNotifications();
	Installer::GetInstance()->printStage();
}
?>
</div>
</div>