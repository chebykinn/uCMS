<?php
class Cache{
	private static $blocks;

	public static function init(){
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

	public static function add($cacheID, $data, $expires = 0, $isRaw = false){
		if( !$isRaw ){
			$data = serialize($data);
		}
		self::$blocks[$cacheID] = array('cid' => $cacheID, "data" => $data, "expires" => $expires, 'raw' => $isRaw, 'created' => time());
		$add = new Query("{cache}");
		$add->insert(self::$blocks[$cacheID])->execute();
	}

	public static function isCached($cacheID){
		return isset(self::$blocks[$cacheID]);
	}

	public static function get($cacheID){
		if( !self::isCached($cacheID) ) return false;
		if( !self::$blocks[$cacheID]['raw'] ){
			$data = unserialize(self::$blocks[$cacheID]['data']);
		}else{
			$data = self::$blocks[$cacheID]['data'];
		}
		return $data;
	}

	public static function set($cacheID, $data, $expires = -1){
		if( !self::isCached($cacheID) ) return false;

		if( !self::$blocks[$cacheID]['raw'] ){
			$data = serialize($data);
		}
		self::$blocks[$cacheID]['data'] = $data;
		if( $expires >= 0 ){
			self::$blocks[$cacheID]['expires'] = $expires;
		}
	}

	public static function update($cacheID){
		$update = new Query("{cache}");
		$update->delete()->where()->condition('cid', '=', $cacheID)->execute();
	}

	public static function cleanAll($blockID = ""){

	}

}
?>