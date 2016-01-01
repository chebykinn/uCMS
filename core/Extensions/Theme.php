<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\Debug;
use uCMS\Core\Setting;
use uCMS\Core\Page;
use uCMS\Core\uCMS;
use uCMS\Core\Block;
use uCMS\Core\Notification;
class Theme extends AbstractExtension{
	const DEFAULT_THEME = "ucms";
	const GENERAL_TEMPLATE = 'general.php';
	const ERROR_TEMPLATE = 'error.php';
	const ERROR_TEMPLATE_NAME = 'error';
	const HTML_TEMPLATE = "core/content/templates/html.php";
	const PAGE_TEMPLATE = "core/content/templates/page.php";
	const VARIABLES_LOAD = "core/content/templates/variables.php";
	const INFO = 'theme.info';
	const PATH = 'content/themes/';
	const CORE_PATH = 'core/content/themes/';
	private static $instance;
	private $title;
	private $themeTemplate = self::GENERAL_TEMPLATE;
	private $regions = array();
	private $errorCode = 0;
	private static $defaultList = array();


	/**
	* @var string $pageTitle An optional title for page, that should be set by some extension
	* or by core, when error occurred.
	* @var string $pageContent An optional content for page, should be just rich formatted text.
	*/
	private $pageTitle, $pageContent;

	public function __construct($name){
		if( empty($name) ) $name = self::DEFAULT_THEME;
		parent::__construct($name);
	}

	public static function SetCurrent($themeName){
		self::$defaultList = array('install', 'ucms', 'admin');
		self::$instance = new self($themeName);
	}

	public static function IsLoaded(){
		return !is_null( self::$instance );
	}

	public static function GetCurrent(){
		if ( is_null( self::$instance ) ){
			Debug::Log($this->tr("Theme is not loaded"), Debug::LOG_CRITICAL);
			return false;
		}
		return self::$instance;
	}

	public static function ReloadTheme($newTheme){
		try{
			Theme::SetCurrent($newTheme);
		}catch(\InvalidArgumentException $e){
			$this->p("[@s]: ".$e->getMessage(), $newTheme);
		}catch(\RuntimeException $e){
			$this->p("[@s]: ".$e->getMessage(), $newTheme);
		}
	}

	public static function LoadErrorPage($errorCode){
		Debug::Log($this->tr("Error @s at: @s", $errorCode,
						Page::GetCurrent()->getURL()), Debug::LOG_WARNING);
		$theme = Setting::get('theme');
		if( empty($theme) ) $theme = self::DEFAULT_THEME;
		if( !self::IsLoaded() || $theme != self::GetCurrent()->getName() ) {
			self::ReloadTheme($theme);
		}
		// TODO: Add HTTP Header
		// TODO: Fix XSS
		self::GetCurrent()->setErrorCode($errorCode);
		self::GetCurrent()->setTitle($this->tr("404 Not Found"));
		self::GetCurrent()->setPageTitle($this->tr("404 Not Found"));
		self::GetCurrent()->setPageContent($this->tr("Page \"@s\" was not found.",
			Page::GetCurrent()->getURL()));
		self::GetCurrent()->setThemeTemplate(self::ERROR_TEMPLATE_NAME);
	}

	public function loadInfo(){
		if( !file_exists($this->getExtensionInfoPath()) ){
			throw new \InvalidArgumentException("Can't get theme information");
		}
		$encodedInfo = file_get_contents($this->getExtensionInfoPath());

		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			//Debug::Log($this->tr("Can't get extension information @s", $this->name), Debug::LOG_ERROR);
			throw new \InvalidArgumentException("Can't get theme information");
		}
		$this->version = $decodedInfo['version'];
		$this->coreVersion = $decodedInfo['coreVersion'];

		$this->dependencies = !empty($decodedInfo['dependencies']) ? $decodedInfo['dependencies'] : "";
		$this->info         = !empty($decodedInfo['info'])         ? $decodedInfo['info']         : "";
		$this->regions      = !empty($decodedInfo['regions']) ? $decodedInfo['regions'] : [];
		$this->blocks       = !empty($decodedInfo['blocks']) ? $decodedInfo['blocks'] : [];
		if( !is_array($this->regions) ) $this->regions = [$this->regions];
	}


	public function getErrorCode(){
		return $this->errorCode;
	}

	public function setErrorCode($code){
		$this->errorCode = (int) $code;
	}

	public static function IsDefault($name){
		return in_array($name, self::$defaultList);
	}

	protected function getRelativePath(){
		return self::IsDefault($this->name) ? self::CORE_PATH : self::PATH;
	}

	protected function getPath(){
		return $this->getRelativePath()."$this->name/";
	}

	protected function getURLPath(){
		return uCMS::GetDirectory().$this->getRelativePath()."$this->name/";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(self::INFO);
	}

	public function getURLFilePath($file){
		return uCMS::GetDirectory().$this->getRelativePath()."$this->name/$file";
	}

	public function load($variables = true){
		if( $variables ){
			include_once(ABSPATH.self::VARIABLES_LOAD);
			// Load blocks
			$blocks = Block::GetList('', $this->getName());
			foreach ($blocks as $regions) {
				foreach ($regions as $block) {
					$block->render();
				}
			}
		}
		$themeLoad = $this->getFilePath("load.php");
		if( file_exists($themeLoad) && is_file($themeLoad) ){
			include_once($themeLoad);
		}
		include_once(ABSPATH.self::PAGE_TEMPLATE);
	}

	public function setThemeTemplate($name){
		$template = $this->getFilePath($this->getInfo($name));
		if( file_exists($template) && is_file($template) ){
			$this->themeTemplate = $this->getInfo($name);
		}
	}


	public function loadStyles(){
		$styleList = $this->getInfo('style');
		if( empty($styleList) ) return;
		if( !is_array($styleList) ) $styleList = [$styleList];
		$extensions = ExtensionHandler::GetList();
		foreach ($extensions as $name) {
			$extension = ExtensionHandler::Get($name);
			$styles = $extension->getInfo('styles');
			if( is_array($styles) && !empty($styles[$this->name]) && is_array($styles[$this->name]) ){
				foreach ($styles[$this->name] as $extStyle) {
					$styleList[] = $extension->getURLFilePath($extStyle);
				}
			}
		}

		foreach ($styleList as $css) {
			$cssHref = "";
			if( file_exists(ABSPATH.$css) ){
				$cssHref = $css;
			}
			if( file_exists($this->getFilePath($css)) ){
				$cssHref = $this->getURLFilePath($css);	
			}
			if( empty($cssHref) ) continue;
			print '<link rel="stylesheet" type="text/css" href="'.$cssHref.'">'."\n";
		}

	}

	public function loadScripts(){
		$scriptList = $this->getInfo('script');
		if( empty($scriptList) ) return;
		if( !is_array($scriptList) ) $scriptList = [$scriptList];
		$extensions = ExtensionHandler::GetList();
		foreach ($extensions as $name) {
			$extension = ExtensionHandler::Get($name);
			$scripts = $extension->getInfo('scripts');
			if( is_array($scripts) && !empty($scripts[$this->name]) && is_array($scripts[$this->name]) ){
				foreach ($scripts[$this->name] as $extStyle) {
					$scriptList[] = $extension->getURLFilePath($extStyle);
				}
			}
		}
		foreach ($scriptList as $file) {
			$scriptSrc = "";
			if( file_exists(ABSPATH.$file) ){
				$scriptSrc = $file;
			}
			if( file_exists($this->getFilePath($file)) ){
				$scriptSrc = $this->getURLFilePath($file);	
			}
			if( empty($scriptSrc) ) continue;
			print '<script type="text/javascript" src="'.$scriptSrc.'"></script>'."\n";
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
		$this->title = $title;
	}

	/**
	* Get current title.
	*
	* Get current title that should be displayed in template 'title' tag.
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
		$blocks = Block::GetList($name);
		foreach ($blocks as $block) {
			$block->printRendered();
		}
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

	/**
	* Get blocks mapping for regions.
	*
	* Get array of blocks that should be added to theme regions.
	*
	* @since 2.0
	* @param none
	* @return array Associative array of regions and blocks in them.
	*/
	public function getBlocksMap(){
		return $this->blocks;
	}

	/**
	* Prepare $value.
	*
	* This method allows templates to print variables without safety concern.
	*
	* @since 2.0
	* @param $value Variable to prepare
	* @api
	* @return void
	*/
	public function prepare($value){
		// TODO: complex rendering
		return htmlspecialchars($value);
	}
}
?>