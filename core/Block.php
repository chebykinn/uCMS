<?php
namespace uCMS\Core;
use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ThemeHandler;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\ORM\Model;
class Block extends Model{
	const SHOW_EXCEPT = 0;
	const SHOW_LISTED = 1;
	const SHOW_MANUAL = 2;

	const CACHE_NONE = 0;
	const CACHE_USER = 1;
	const CACHE_GROUP = 2;
	const CACHE_ACTION = 3;
	const CACHE_GLOBAL = 4;

	const DISABLED = 0;
	const ENABLED = 1;
	private static $list;

	public function init(){
		$this->tableName('blocks');
		$this->primaryKey('bid');
	}

	public static function Load(){
		// Load blocks data for current action and theme
		$action = Page::GetCurrent()->getAction();
		if( $action === ControlPanel::ACTION ){
			$action .= '/'.ControlPanel::GetAction();
		}

		$theme = Theme::GetCurrent()->getName();
		$blocks = (new Block())->find([
			'where' => [
				['status', '=', 1], ['theme', '=', $theme], ['visibility', '=', 1], ['actions', 'LIKE', "%$action%", 'or'],
				['status', '=', 1], ['theme', '=', $theme], ['visibility', '=', 0], ['actions', 'NOT LIKE', "%$action%", 'or'],
				['status', '=', 1], ['theme', '=', $theme], ['visibility', '=', 2, 'or']
			],
			'orders' => ['position' => 'ASC']
		]);
		foreach ($blocks as $block) {
			self::$list[$block->theme][$block->region][$block->name] = $block;

		}
	}
	
	public function render($blockRow){

		$ownerExtension = ExtensionHandler::Get($blockRow->owner);

		$loadFile = $ownerExtension->getFilePath("$blockRow->name.load.php");
		if( true && file_exists($loadFile) ) { // TODO: add cache check
			include_once $loadFile;
		}else{
			// get from cache or do nothing
		}
		$template = "templates/$blockRow->name.php";
		$theme = new Theme($blockRow->theme);
		// Caution: includes can overwrite previously declared variables 
		ob_start();

		include(ABSPATH.Theme::VARIABLES_LOAD);
		
		if( file_exists($theme->getFilePath($template)) ){
			include_once $theme->getFilePath($template);
		}else if( file_exists($ownerExtension->getFilePath($template)) ){
			include_once $ownerExtension->getFilePath($template);
		}
		$blockRow->renderedHTML = ob_get_clean();

		// TODO: cache this render
		return !empty($blockRow->renderedHTML);
	}

	public function printRendered($row){
		// Print rendered block content
		if( !empty($row->renderedHTML) ){
			print $row->renderedHTML;
		}
	}

	private function prepareData($row){
		if( !ThemeHandler::IsExists($row->theme) ) $row->theme = "";
		if( empty($row->theme) ) $row->theme = Settings::Get('theme');

		if( $row->status == self::ENABLED ){
			$row->status = ($row->region != "") ? self::ENABLED : self::DISABLED;
		}

		$row->owner = Tools::GetCurrentOwner();

		if( $row->visibility < self::SHOW_EXCEPT ){
			$row->visibility = self::SHOW_EXCEPT;
		}

		if( $row->visibility > self::SHOW_MANUAL ){
			$row->visibility = self::SHOW_MANUAL;
		}

		if( $row->cache < self::CACHE_NONE ){
			$row->cache = self::CACHE_NONE;
		}

		if( $row->cache > self::CACHE_GLOBAL ){
			$row->cache = self::CACHE_GLOBAL;
		}

		if( $row->position < 0 ){
			$row->position = 0;
		}
	}

	public function create($row){
		$this->prepareData($row);
		$duplicate = (new Block())->count([
			'theme'  => $row->theme,
			'region' => $row->region,
			'name'   => $row->name
		]);

		if( !empty($duplicate) ) return false;

		$result = parent::create($row);
		if( $result ){
			Settings::Increment("blocks_amount");
		}
		return $result;
	}

	public function update($row){
		$this->prepareData($row);

		$duplicate = (new Block())->count([
			'theme'  => $row->theme,
			'region' => $row->region,
			'name'   => $row->name
		]);

		if( !empty($duplicate) ) return false;

		$result = parent::update($row);
		return $result;
	}

	public function delete($row){
		$result = parent::delete($row);
		if( $result ){
			Settings::Decrement("blocks_amount");
		}
		return $result;
	}

	public static function GetList($region = "", $theme = ""){
		if ( !ThemeHandler::IsExists($theme) ){
			$theme = Theme::GetCurrent()->getName();
		}
		if( isset(self::$list[$theme]) ){
			if( !empty($region) ){
				if( isset(self::$list[$theme][$region]) ){
					return self::$list[$theme][$region];
				}
				return array();
			}
			return self::$list[$theme];
		}
		return array();
	}
}
?>
