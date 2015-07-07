
<div id="wrapper">
<a href="#sidebar" class="show-sidebar"></a>
<div id="sidebar">
	<?php
		echo ControlPanel::printSidebar();
	?>
	<ul>
		<li><a href="<?php echo UCMS_DIR; ?>"><?php p('Go to site'); ?></a></li>
	</ul>
</div>

<div id="content">
<?php
$currentAction = ControlPanel::GetAction();
// var_dump($currentAction);

// exit;

	switch ( $currentAction ) {

		case 'home':
			$this->loadTemplate('index');
		break;
	
		case 'settings':
			$extensionAction = ControlPanel::GetSettingsAction();
			if( !empty($extensionAction) ){
				$settingsAction = 'settings/'.$extensionAction;
				$extension = Extensions::GetExtensionByAdminAction($settingsAction);
				if( is_object($extension) ){
					$pageFile = $extension->getAdminPageFile($settingsAction);
				}
				if( !empty($extension) && !empty($pageFile) ){
					include_once($pageFile);
				}else{
					Debug::Log(tr("Unable to load admin page for action: @s", $settingsAction), UC_LOG_ERROR);
					$settingsPage = Page::FromAction(ADMIN_ACTION, 'settings');
					$settingsPage->go();
				}
			}else{
				$this->loadTemplate('settings');
			}
		break;
		
		default:
			if( in_array($currentAction, ControlPanel::GetDefaultActions()) ){
				$this->loadTemplate($currentAction);
			}else{
				$extension = Extensions::getExtensionByAdminAction($currentAction);
				if( is_object($extension) ){
					$pageFile = $extension->getAdminPageFile($currentAction);
				}
				if( !empty($extension) && !empty($pageFile) ){
					include $pageFile;
				}else{
					Debug::Log(tr("Unable to load admin page for action: @s", $currentAction), UC_LOG_ERROR);
					$homePage = Page::FromAction(ADMIN_ACTION);
					$homePage->go();
				}
			}
		break;
	}
?>
</div>
<div id="footer">
	<?php p("IVaN4B's μCMS © 2011-@s Queries: @s. Load time: @s seconds. <span class=\"ucms-version\">Version: @s</span>", 
	date('Y'), DatabaseConnection::GetDefault()->getQueriesCount(),
			uCMS::getInstance()->getLoadTime(), CORE_VERSION); ?>
</div>
</div>