<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\Tools;
use uCMS\Core\uCMS;
use uCMS\Core\Settings;

class ThemeHandler{
	const PATH = 'content/themes/';
	const CORE_PATH = 'core/content/themes/';
	static $defaultList = ['install', 'ucms', 'admin'];
	public static function SetCurrent($themeName){
		self::$defaultList = array('install', 'ucms', 'admin');
		self::$instance = new self($themeName);
	}

	public static function IsCurrent($name){
		$current = Settings::Get(Settings::THEME);
		return ( $current === $name );
	}

	public static function ChangeTheme($name){
		$current = Settings::Get(Settings::THEME);
		if( $current === $name ) return false;
		if( !self::IsExists($name) ) return false;

		Tools::OverrideOwner();
		Settings::Update(Settings::THEME, $name);
		return true;
	}

	public static function IsExists($name){
		return in_array($name, self::GetList());
	}

	final public static function GetList(){
		$names = [];
		$extdirs = file_exists(ABSPATH.self::PATH) ? scandir(ABSPATH.self::PATH) : [];
		$directories = array_merge(scandir(ABSPATH.self::CORE_PATH), $extdirs);
		foreach ($directories as $theme) {
			if( self::IsTheme($theme) ){
				$names[] = $theme;
			}
		}
		return $names;
	}

	final public static function IsTheme($name){
		$path = ABSPATH.(self::IsDefault($name) ? self::CORE_PATH : self::PATH).$name;

		if( is_dir($path) ){
			return file_exists($path.'/'.Theme::INFO);
		}
		return false;
	}

	final public static function IsDefault($name){
		return in_array($name, self::$defaultList);
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

	public static function GetTemplate($name, $url = false, $nophp = false){
		$path = 'content/templates/';
		$corePath = 'core/'.$path;
		$coreFile = $corePath.$name.($nophp ? '' : '.php');
		$file = $path.$name.($nophp ? '' : '.php');
		$template = "";
		if ( file_exists(ABSPATH.$coreFile) && is_file(ABSPATH.$coreFile) ){
			$template = (!$url ? ABSPATH : uCMS::GetDirectory()).$coreFile;
		}

		if ( file_exists(ABSPATH.$file) && is_file(ABSPATH.$file) ){
			$template = (!$url ? ABSPATH : uCMS::GetDirectory()).$file;
		}
		return $template;
	}

	public static function LoadTemplate($name){
		$file = self::GetTemplate($name);
		if( empty($file) ){
			Debug::Log(tr("Unable to load template @s", $name), Debug::LOG_ERROR);
			return false;
		}

		include_once $file;
		return true;
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
		// TODO: Fix XSS
		self::GetCurrent()->setErrorCode($errorCode);
		self::GetCurrent()->setTitle(tr("404 Not Found"));
		self::GetCurrent()->setPageTitle(tr("404 Not Found"));
		self::GetCurrent()->setPageContent(tr("Page \"@s\" was not found.",
			Page::GetCurrent()->getURL()));
		self::GetCurrent()->setThemeTemplate(self::ERROR_TEMPLATE_NAME);
	}
}
?>