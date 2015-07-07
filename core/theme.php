<?php
class Theme extends Extension{
	private static $instance;
	private $title;
	private $themeTemplate = GENERAL_TEMPLATE;
	private $regions = array();
	private $errorCode = 0;

	public static function SetCurrent($themeName){
		self::$instance = new self($themeName);
	}

	public static function IsLoaded(){
		return !is_null( self::$instance );
	}

	public static function GetCurrent(){
		if ( is_null( self::$instance ) ){
			log_add(tr("Theme is not loaded"), UC_LOG_CRITICAL);
			return false;
		}
		return self::$instance;
	}

	public static function ReloadTheme($newTheme){
		try{
			Theme::SetCurrent($newTheme);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $newTheme);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $newTheme);
		}
	}

	public static function LoadErrorPage($errorCode){
		Debug::Log(tr("Error @s at action: @s", $errorCode,
						Page::GetCurrent()->getAction()), UC_LOG_WARNING);
		$theme = Settings::get('theme');
		if( empty($theme) ) $theme = DEFAULT_THEME;
		if( !self::IsLoaded() || $theme != self::GetCurrent()->getName() ) {
			self::ReloadTheme($theme);
		}
		self::GetCurrent()->setErrorCode($errorCode);
		self::GetCurrent()->setTitle(tr("404 Not Found"));
		self::GetCurrent()->setThemeTemplate(ERROR_TEMPLATE_NAME);
	}

	public function loadInfo(){
		$encodedInfo = @file_get_contents($this->getExtensionInfoPath());
		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			Debug::Log(tr("Can't get theme information @s", $this->name), UC_LOG_ERROR);
			throw new InvalidArgumentException("Can't get theme information");
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
		return THEMES_PATH."$this->name/";
	}

	protected function getURLPath(){
		return THEMES_URL_PATH."$this->name/";
	}

	protected function getFilePath($file){
		if( !empty($file) ){
			return THEMES_PATH."$this->name/$file";
		}
		return "";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(THEME_INFO);
	}

	public function getURLFilePath($file){
		return THEMES_URL_PATH."$this->name/$file";
	}

	public function load(){
		include_once(PAGE_TEMPLATE);
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
		/**
		* @todo add default css'es if needed
		*/
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
		/**
		* @todo add default scripts'es if needed
		*/
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

	public function setTitle($title){
		$this->title = htmlspecialchars($title);
	}

	public function getTitle(){
		return $this->title;
	}

	public function printRegionBlocks($name){
		
		if( in_array($name, $this->regions) ) return "";
		Debug::PrintVar($name);
	}
}
?>