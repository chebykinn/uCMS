<?php
namespace uCMS\Core\Extensions\Entries\Entry;
class EntryType{
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
}
?>