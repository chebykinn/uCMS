<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\uCMS;
use uCMS\Core\Setting;
use uCMS\Core\Block;
use uCMS\Core\Object;


class ThemeHandler extends Object{
	const PATH = 'content/themes/';
	const CORE_PATH = 'core/content/themes/';
	static $defaultList = ['install', 'ucms', 'admin'];
	public static function SetCurrent($themeName){
		self::$defaultList = array('install', 'ucms', 'admin');
		self::$instance = new self($themeName);
	}

	public static function IsCurrent($name){
		$current = Setting::Get(Setting::THEME);
		return ( $current === $name );
	}

	public static function ChangeTheme($name){
		$current = Setting::Get(Setting::THEME);
		if( $current === $name ) return false;
		if( !self::IsExists($name) ) return false;

		// Check blocks for new theme
		// TODO: consider deleting old blocks
		$blocks = (new Block())->count(['theme' => $name]);

		if( $blocks == 0 ){
			try{
				$theme = new Theme($name);
				$list = $theme->getBlocksMap();
				foreach ($list as $region => $blocks) {
					if( !is_array($blocks) ) $blocks = [$blocks];
					foreach ($blocks as $blockName) {
						$source = (new Block())->find(['name' => $blockName]);
						if( empty($source) ) continue;
						if( !is_array($source) ) $source = [$source];
						
						$block = (new Block())->emptyRow();
						$block->name = $blockName;
						$block->region = $region;
						$block->status = Block::ENABLED;
						$block->theme = $name;
						$block->owner = $source[0]->owner;
						$block->title = $source[0]->title;
						$block->visibility = $source[0]->visibility;
						$block->actions = $source[0]->actions;
						$block->create();
					}
				}
			}catch(\Exception $e){
				return false;
			}
		}

		Setting::UpdateValue(Setting::THEME, $name, new self());

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
			$this->p("[@s]: ".$e->getMessage(), $newTheme);
		}catch(\RuntimeException $e){
			$this->p("[@s]: ".$e->getMessage(), $newTheme);
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
			Debug::Log($this->tr("Unable to load template @s", $name), Debug::LOG_ERROR, $this);
			return false;
		}

		include_once $file;
		return true;
	}

	public static function LoadErrorPage($errorCode){
		Debug::Log($this->tr("Error @s at: @s", $errorCode,
						Page::GetCurrent()->getURL()), Debug::LOG_WARNING, $this);
		$theme = Setting::Get(Setting::THEME);
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
}
?>