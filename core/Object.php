<?php
namespace uCMS\Core;
use uCMS\Core\Localization\Language;

class Object {
	protected $_name;
	protected $_settings;

	public function __construct(){
		$this->_name = (new \ReflectionClass($this))->getShortName();
	}

	final public function getObjectName(){
		return $this->_name;
	}

	public function addSetting($name, $value){
	}

	public function updateSetting($name, $value){
	}

	public function deleteSetting($name){
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

	public function tr($string){
		$args = func_get_args();
		return call_user_func_array([Language::GetCurrent(), 'get'], $args);
	}

	public function p($string){
		print call_user_func_array([$this, 'tr'], func_get_args());
	}

	public static function Translate($string){
		$obj = new self();
		return call_user_func_array([$obj, 'tr'], func_get_args());
	}
}
?>