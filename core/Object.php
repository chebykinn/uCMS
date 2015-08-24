<?php
namespace uCMS\Core;
abstract class Object{
	const MODE_FULL = 'full';
	const MODE_BASE = 'base';
	const MODE_MANUAL = 'manual';
	const LIMIT = 10;
	public static function FromArray($data, $prefixes = array(), $namespaces = array(), $returnClass = "\\uCMS\\Core\\Database\\Object"){
		$nestedData = array();
		$args = func_get_args();
		// \uCMS\Core\Debug::PrintVar($args);
		if( is_array($data) ){
			$entry = new $returnClass();
			foreach ($data as $var => $value) {
				if( strpos($var, ":") !== false ){
					// If array key have a prefix, it means, that it is a object to deserialize
					$nested = explode(":", $var);
					$prefix = $nested[0];
					if( in_array($prefix, $prefixes) ){
						$key = array_search($prefix, $prefixes);
						$var = $nested[1];
						$nestedData[$key][$var] = $value;
					}
				}else{
					$entry->$var = $value;
				}
			}
			foreach ($nestedData as $var => $data) {
				$className = $prefixes[$var];
				$fullClass = $namespaces[$className]."\\$className";
				if( is_subclass_of($fullClass, "\\uCMS\\Core\\Object") ){
					$entry->$var = $fullClass::FromArray($data);
				}
				# code...
			}
			return $entry;
		}
		return null;
	}

	public function getID(){
		return $this->oid;
	}

	public function getName(){
		return $this->name;
	}

	public static function Add($object){

	}

	public static function Update($object){

	}

	public static function Delete($object){

	}

	public static function GetList($mode = self::MODE_BASE, $type = "", $sort = array(), $start = 0, $limit = self::LIMIT, $condition = "", $columns = array()){
	 	
	}
}
?>