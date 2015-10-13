<?php
namespace uCMS\Core;
use uCMS\Core\Database\Query;
class Cache{
	private static $blocks;

	public static function Init(){
		$query = new Query("{cache}");
		$data = $query->select("*", true)->execute();
		foreach ($data as $row) {
			self::$blocks[$row['cid']] = array('cid' => $row['cid'], "data" => $row['data'], "expires" => $row['expires'], 'raw' => $row['raw'], 'created' => $row['created']);
		}
		/**
		* @todo cache limit
		*/
	}

	public static function beginCache($cacheID, $expires){
		
	}

	public static function endCache(){
		
	}

	public static function Add($cacheID, $data, $expires = 0, $isRaw = false){
		if( !$isRaw ){
			$data = serialize($data);
		}
		self::$blocks[$cacheID] = array('cid' => $cacheID, "data" => $data, "expires" => $expires, 'raw' => $isRaw, 'created' => time());
		$add = new Query("{cache}");
		$add->insert(self::$blocks[$cacheID])->execute();
	}

	public static function IsCached($cacheID){
		return isset(self::$blocks[$cacheID]);
	}

	public static function Get($cacheID){
		if( !self::isCached($cacheID) ) return false;
		if( !self::$blocks[$cacheID]['raw'] ){
			$data = unserialize(self::$blocks[$cacheID]['data']);
		}else{
			$data = self::$blocks[$cacheID]['data'];
		}
		return $data;
	}

	public static function Set($cacheID, $data, $expires = -1){
		if( !self::isCached($cacheID) ) return false;

		if( !self::$blocks[$cacheID]['raw'] ){
			$data = serialize($data);
		}
		self::$blocks[$cacheID]['data'] = $data;
		if( $expires >= 0 ){
			self::$blocks[$cacheID]['expires'] = $expires;
		}
	}

	public static function Update($cacheID){
		$update = new Query("{cache}");
		$update->delete()->where()->condition('cid', '=', $cacheID)->execute();
	}

	public static function CleanAll($blockID = ""){

	}

}
?>