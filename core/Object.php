<?php
namespace uCMS\Core;
use uCMS\Core\Localization\Language;
use uCMS\Core\Extensions\ExtensionHandler;

class Object {
	protected $_name;
	protected $_owner = NULL;
	protected $_package = NULL;
	protected $_namespace = NULL;

	public function __construct($owner = NULL){
		if( !is_subclass_of($owner, 'uCMS\\Core\\Object') && $owner != NULL ) return false;
		if( is_subclass_of($owner, 'uCMS\\Core\\Object') ){
			$this->_owner = $owner;
		}
		$reflection = new \ReflectionClass($this);
		$this->_name = mb_strtolower($reflection->getShortName());
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

	public function prepareHtml($value){
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
			$package = $this->_name;
		}
		return $package;
	}
}
?>