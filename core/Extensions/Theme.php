<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\Debug;
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Loader;
use uCMS\Core\uCMS;
use uCMS\Core\Notification;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\Extensions\Users\User;
class Theme extends Extension{
	const DEFAULT_THEME = "ucms";
	const GENERAL_TEMPLATE = 'general.php';
	const ERROR_TEMPLATE = 'error.php';
	const ERROR_TEMPLATE_NAME = 'error';
	const HTML_TEMPLATE = "content/templates/html.php";
	const PAGE_TEMPLATE = "content/templates/page.php";
	const INFO = 'theme.info';
	const PATH = 'content/themes/';
	private static $instance;
	private $title;
	private $themeTemplate = self::GENERAL_TEMPLATE;
	private $regions = array();
	private $errorCode = 0;

	/**
	* @var string $pageTitle An optional title for page, that should be set by some extension
	* or by core, when error occurred.
	* @var string $pageContent An optional content for page, should be just rich formatted text.
	*/
	private $pageTitle, $pageContent;

	/**
	* @var mixed[] $data An array representing useful variables for theme
	*/
	private $data;

	public static function SetCurrent($themeName){
		self::$instance = new self($themeName);
	}

	public static function IsLoaded(){
		return !is_null( self::$instance );
	}

	public static function GetCurrent(){
		if ( is_null( self::$instance ) ){
			log_add(tr("Theme is not loaded"), Debug::LOG_CRITICAL);
			return false;
		}
		return self::$instance;
	}

	public static function ReloadTheme($newTheme){
		try{
			Theme::SetCurrent($newTheme);
		}catch(\InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $newTheme);
		}catch(\RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $newTheme);
		}
	}

	public static function LoadErrorPage($errorCode){
		Debug::Log(tr("Error @s at: @s", $errorCode,
						Page::GetCurrent()->getURL()), Debug::LOG_WARNING);
		$theme = Settings::get('theme');
		if( empty($theme) ) $theme = self::DEFAULT_THEME;
		if( !self::IsLoaded() || $theme != self::GetCurrent()->getName() ) {
			self::ReloadTheme($theme);
		}
		// TODO: Add HTTP Header
		self::GetCurrent()->setErrorCode($errorCode);
		self::GetCurrent()->setTitle(tr("404 Not Found"));
		self::GetCurrent()->setPageTitle(tr("404 Not Found"));
		self::GetCurrent()->setPageContent(tr("Page \"@s\" was not found.",
			Page::GetCurrent()->getURL()));
		self::GetCurrent()->setThemeTemplate(self::ERROR_TEMPLATE_NAME);
	}

	public function loadInfo(){
		$encodedInfo = @file_get_contents($this->getExtensionInfoPath());
		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			Debug::Log(tr("Can't get theme information @s", $this->name), Debug::LOG_ERROR);
			throw new \InvalidArgumentException("Can't get theme information");
		}
		$this->version = $decodedInfo['version'];
		$this->coreVersion = $decodedInfo['coreVersion'];

		$this->dependencies = !empty($decodedInfo['dependencies']) ? $decodedInfo['dependencies'] : "";
		$this->info         = !empty($decodedInfo['info'])         ? $decodedInfo['info']         : "";
		$this->regions      = !empty($decodedInfo['regions']) ? $decodedInfo['regions'] : array();
		if( !is_array($this->regions) ) $this->regions = array($this->regions);
	}


	public function getErrorCode(){
		return $this->errorCode;
	}

	public function setErrorCode($code){
		$this->errorCode = (int) $code;
	}

	protected function getPath(){
		return ABSPATH.self::PATH."$this->name/";
	}

	protected function getURLPath(){
		return UCMS_DIR.self::PATH."$this->name/";
	}

	protected function getFilePath($file){
		if( !empty($file) ){
			return ABSPATH.self::PATH."$this->name/$file";
		}
		return "";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(self::INFO);
	}

	public function getURLFilePath($file){
		return UCMS_DIR.self::PATH."$this->name/$file";
	}

	public function load(){
		$this->loadData();
		include_once(self::PAGE_TEMPLATE);
	}

	public function setThemeTemplate($name){
		if( file_exists($this->getFilePath($this->getInfo($name))) ){
			$this->themeTemplate = $this->getInfo($name);
		}
	}

	public function loadTemplate($name){
		$this->includeFile($name.'.php');
	}

	public function loadStyles(){
		// TODO: Add default uCMS styles
		$style = $this->getInfo('style');
		if( empty($style) ) return;
		if( !is_array($style) ) $style = array($style);
		foreach ($style as $css) {
			if( file_exists($this->getFilePath($css)) ){
				$cssHref = $this->getURLFilePath($css);
				print '<link rel="stylesheet" type="text/css" href="'.$cssHref.'">'."\n";
			}
		}

	}

	public function loadScripts(){
		// TODO: Add default uCMS scripts
		$script = $this->getInfo('script');
		if( empty($script) ) return;
		if( !is_array($script) ) $script = array($script);
		foreach ($script as $file) {
			if( file_exists($this->getFilePath($file)) ){
				$scriptSrc = $this->getURLFilePath($file);
				print '<script type="text/javascript" src="'.$scriptSrc.'"></script>'."\n";
			}
		}

	}

	public function loadBlock($name){
		$block = $this->getFilePath($this->getInfo($name));
		if( file_exists($block) ){
			$this->includeFile($this->getInfo($name));
		}
	}

	public function IsTitleSet(){
		return !empty($this->title);
	}

	/**
	* Change current title.
	*
	* This method allows you to update page title, though it should be called before template loading.
	*
	* @api
	* @since 2.0
	* @param string $title New title for page.
	* @return void
	*/
	public function setTitle($title){
		$this->title = htmlspecialchars($title);
	}

	/**
	* Get current title.
	*
	* Get current title that should be displayed in template <title> tag.
	*
	* @api
	* @since 2.0
	* @param none
	* @return string Current title.
	*/
	public function getTitle(){
		return $this->title;
	}

	/**
	* Prints all block attached to region $name.
	* 
	* This method selects blocks that should be displayed in given region and at current action,
	* and prints them as they are define the render() method.
	*
	* @api
	* @since 2.0
	* @param string $name The name of region.
	* @return void
	*/
	public function region($name){
		if( !in_array($name, $this->regions) ) return "";
		// Debug::PrintVar($name);
	}

	/**
	* Sets an array of variables to display.
	*
	* This method prepares an array of variables that should be used by theme to display site
	* information or to control data flow.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	private function loadData(){
		$this->data['action'] = Page::GetCurrent()->getAction();
		$this->data['admin-action'] = ControlPanel::GetAction();
		$this->data['site-name'] = Settings::Get("site_title");
		$this->data['site-description'] = Settings::Get("site_description");
		$this->data['queries-count'] = DatabaseConnection::GetDefault()->getQueriesCount(); //?
		$this->data['load-time'] = Loader::GetInstance()->getLoadTime(); //?
		$this->data['core-version'] = uCMS::CORE_VERSION; //?
		$this->data['current-user'] = User::Current(); //?
		$this->data['home-page'] = Page::Home()->getURL();
	}

	/**
	* Get the array of prepared variables.
	*
	* This method is used to get a variable from special array, which is used to provide site
	* information to current theme.
	*
	* @api
	* @since 2.0
	* @param string $name The name of variable.
	* @return mixed The variable contents or empty string.
	*/
	public function getVar($name){
		if( isset($this->data[$name]) ){
			return $this->data[$name];
		}
		return "";
	}

	/**
	* Print all pending notifications.
	* 
	* This method sets a place where all notifications should be printed.
	*
	* @api
	* @since 2.0
	* @param none
	* @return void
	*/
	public function showNotifications(){
		// TODO: Add customization options
		Notification::ShowPending();
	}

	/**
	* Get current page title.
	*
	* This method allows you to print current page title (if set) in your theme.
	*
	* @api
	* @since 2.0
	* @param none
	* @return string The title of current page or empty string if not set.
	*/
	public function pageTitle(){
		return $this->pageTitle;
	}
	
	/**
	* Get current page content.
	*
	* This method allows you to print current page content (if set) in your theme.
	*
	* @api
	* @since 2.0
	* @param none
	* @return string The content of current page or empty string if not set.
	*/
	public function pageContent(){
		return $this->pageContent;
	}

	/**
	* Sets the page title.
	*
	* This method allows extensions to set page title for theme template.
	*
	* @api
	* @since 2.0
	* @param string $title A title for page.
	* @return void
	*/
	public function setPageTitle($title){
		// TODO: XSS protection
		$this->pageTitle = $title;
	}

	/**
	* Sets the page content.
	*
	* This method allows extensions to set page content for theme template.
	*
	* @api
	* @since 2.0
	* @param string $content A content for page.
	* @return void
	*/
	public function setPageContent($content){
		// TODO: XSS protection
		$this->pageContent = $content;
	}
}
?>