<?php
namespace uCMS\Core\Extensions\Entries;

use uCMS\Core\Object;
class EntryType extends Object{
	/**
	 * ID of type.
	 *
	 * @var int Type ID.
	*/
	var $type;

	/**
	 * The name of type.
	 *
	 * @var string Type name.
	*/
	var $name;
	
	/**
	 * The description of type.
	 *
	 * @var string Type description.
	*/
	var $description;
	
	/**
	 * The owner of type.
	 *
	 * @var string Type owner.
	*/
	var $owner;

	/**
	 * The alias of type.
	 *
	 * @var string Type alias.
	*/
	var $alias;
	var $comments;
	var $uid;
	var $gid;
	var $permission;
	var $terms;

	public static function FromArray($data, $prefixes = array(), $namespaces = array(), $returnClass = "\\uCMS\\Core\\Extensions\\Entries\\EntryType"){
		// TODO: Terms
		$type = parent::FromArray($data, $prefixes, $namespaces, $returnClass);
		return $type;
	}

	public function getID(){
		return $this->type;
	}

	public function getName(){
		return $this->name;
	}
}
?>