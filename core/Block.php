<?php
namespace uCMS\Core;
use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Theme;
class Block{
	const SHOW_EXCEPT = 0;
	const SHOW_LISTED = 1;
	const SHOW_MANUAL = 2;
	private $bid;
	private $name;
	private $owner;
	private $status;
	private $theme;
	private $region;
	private $cache;
	private $renderedHTML;
	private $visibility; // 0 - show except listed actions, 1 - show at listed actions, 2 - own code to determine pages
	private $actions;
	private static $list;

	public static function Init(){
		//load blocks data
		// status will be zero if theme or region are not set
		$action = Page::GetCurrent()->getAction();
		$query = new Query("SELECT * FROM {blocks} 
			WHERE status = 1 AND 
			((visibility = 0 AND (actions = '' OR actions NOT LIKE '%:action%')) 
			OR (visibility = 1 AND (actions <> '' OR actions LIKE '%:action%'))
			OR visibility = 2)", array(":action" => $action));
		// TODO: actions selection
		$blocksData = $query->execute();
		foreach ($blocksData as $data) {
			# code...
			$block = self::FromArray($data);
		// \uCMS\Core\Debug::PrintVar($block);
			self::$list[$block->theme][$block->region][$block->name] = $block;
		}
	}

	public static function FromArray($data){
		if( is_array($data) ){
			$block = new self();
			$fields = array_keys(get_object_vars($block));
			foreach ($data as $key => $value) {
				if( in_array($key, $fields) ){
					$block->$key = $value;
				}
			}
			return $block;
		}
	}

	public static function Shutdown(){

	}
	
	public function _construct($name = ""){
		if( !empty($name) ){
			$query = new Query("{blocks}");
			$data = $query->select("*")->where()->condition('name', '=', $name)->execute();
			if( !empty($data) ){
				$block = $data[0];
				$this->bid = $block['bid'];
				$this->name = $block['name'];
				$this->owner = $block['owner'];
				$this->status = $block['status'];
				$this->theme = $block['theme'];
				$this->region = $block['region'];
				$this->cache = $block['cache'];
				$this->visibility = $block['visibility'];
				$this->actions = $block['actions'];
			}
		}
	}

	public function getName(){
		return $this->name;
	}

	public function getRegion(){
		return $this->region;
	}

	public function render(){
		//load data
		//$data from cache or
		$ownerExtension = Extension::Get($this->owner);

		$loadFile = $ownerExtension->getFilePath("$this->name.load.php");
		if( true && file_exists($loadFile) ) { // TODO: add cache check
			include_once $loadFile;
		}else{
			// get from cache or do nothing
		}
		$template = "templates/$this->name.php";
		$theme = new Theme($this->theme);
		ob_start();
		if( file_exists($theme->getFilePath($template)) ){
			include_once $theme->getFilePath($template);
		}else if( file_exists($ownerExtension->getFilePath($template)) ){
			include_once $ownerExtension->getFilePath($template);
		}
		$this->renderedHTML = ob_get_clean();
		// TODO: cache this render
		return !empty($this->renderedHTML);
		//load template from theme or extension and render it
	}

	public function printRendered(){
		// Print rendered block content
		if( !empty($this->renderedHTML) ){
			print $this->renderedHTML;
		}
	}

	public static function Add($name, $region = "", $theme = "", $position = -1, $visibility = self::SHOW_EXCEPT, $actions = "", $cache = 0){

		if( !Theme::IsExists($theme) ) $theme = "";
		if( empty($theme) ) $theme = Settings::Get('theme');
		// TODO: region check
		// TODO: Check if block exists
		$status = ($theme != "" && $region != "") ? 1 : 0;
		$owner = Tools::GetCurrentOwner();
		$query = new Query("{blocks}");
		$add = $query->insert( 
			array(
				"name"       => $name,
				"owner"      => $owner,
				"status"     => $status,
				"theme"      => $theme,
				"region"     => $region,
				"visibility" => $visibility,
				"actions"    => $actions,
				"cache"      => $cache,
				"position"   => $position				
			), true
		)->execute();
		Settings::Increment("blocks_amount");
		return $add;
	}

	public static function Update($name, $theme = "", $region = "", $position = -1, $visibility = -1, $actions = "", $cache = -1){
		
	}

	public static function Delete($name){

	}

	public static function Get($theme, $region, $name){
		$owner = Tools::GetCurrentOwner(); 
		if( isset(self::$list[$theme][$region][$name])
			&& self::$list[$theme][$region][$name]->owner === $owner ){
			return self::$list[$theme][$region][$name];
		}else if( !empty($name) ){
			// select from database
			$query = new Query("{blocks}");
			$blockData = $query->select("*")->where()->condition("name", "=", $name)
			 ->_and()->condition("owner", "=", $owner)
			 ->_and()->condition("theme", "=", $theme)
			 ->_and()->condition("region", "=", $region)
			 ->execute();
			if( !empty($blockData) ){
				$block = self::FromArray($blockData);
				return $block;
			}
		}
	}

	public static function GetList($region = "", $theme = ""){
		if ( !Theme::IsExists($theme) ){
			$theme = Tools::GetCurrentOwner();
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