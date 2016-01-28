<?php
namespace uCMS\Core;
use uCMS\Core\Localization\Language;
use uCMS\Core\Extensions\ExtensionHandler;

class Object {
	protected $_name;
	protected $_owner = NULL;
	protected $_package = NULL;
	protected $_namespace = NULL;
	const CORE_PACKAGE = 'core';

	public function __construct(Object $owner = NULL){
		$this->_owner = $owner;
		$reflection = new \ReflectionClass($this);
		$this->_name = $reflection->getShortName();
		$this->_namespace = $reflection->getNamespaceName();
		$this->_package = $this->findPackage();
	}

	final public function getOwner(){
		return $this->_owner;
	}

	final public function getPackage(){
		return $this->_package;
	}

	final public function getNamespace(){
		return $this->_namespace;
	}

	final public function getObjectName(){
		return $this->_name;
	}

	final public function assignOwner(Object $owner){
		$this->_owner = $owner;
	}

	public function prepare($value){
		return htmlspecialchars($value);
	}

	public function prepareSql($value){
		if($value == "") return false;
		$value = implode("", explode( "\\", $value));
		$value = stripslashes($value);
		$value = addcslashes($value, '%');
		return $value;
	}

	final public function tr($string){
		$args = func_get_args();
		if( !Language::IsLoaded() ){
			$args = array_slice($args, 1);
			if( count($args) > 0 ){
				foreach ($args as $arg) {
					$patt[] = "/@s/";
				}
				$string = preg_replace($patt, $args, $string, 1);
			}
			return $string;
		}
		return call_user_func_array([Language::GetCurrent(), 'get'], $args);
	}

	final public function p($string){
		print call_user_func_array([$this, 'tr'], func_get_args());
	}

	final public static function Translate($string){
		$obj = new self();
		return call_user_func_array([$obj, 'tr'], func_get_args());
	}

	private function findPackage(){
		$package = 'core';
		$extensionClasses = ExtensionHandler::GetClasses();
		$extensionNamespace = 'uCMS\\Core\\Extensions';
		if( mb_strpos($this->getNamespace(), $extensionNamespace) !== false
			&& !in_array($this->_name, $extensionClasses) ){
			$package = str_replace($extensionNamespace.'\\', '', $this->getNamespace());
			if( mb_strpos($package, "\\") !== false ){
				$package = mb_substr($package, 0, mb_strpos($package, "\\"));
			}
			$package = mb_strtolower($package);
		}
		return $package;
	}
}
?>