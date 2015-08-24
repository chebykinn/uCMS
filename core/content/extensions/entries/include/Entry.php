<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Settings;
use uCMS\Core\Object;
class Entry extends Object{
	const DRAFT = 0;
	const PUBLISHED = 1;
	const PINNED = 2;
	var $eid;
	var $author;
	var $type;
	var $status;
	var $comments;
	var $title;
	var $alias;
	var $content;
	var $language;
	var $created;
	var $changed;

	var $accessUid;
	var $accessGid;
	var $accessAllow;

	public function __construct($id = 0){

	}

	public static function FromArray($data, $prefixes = array(), $namespaces = array(), $returnClass = "\\uCMS\\Core\\Extensions\\Entries\\Entry"){
		$prefixes = array(
			'author' => 'User',
			'terms'  => 'Term',
			'type'   => 'EntryType'
		);
		$namespaces = array(
			'User' => "\\uCMS\\Core\\Extensions\\Users",
			'Term' => __NAMESPACE__,
			'EntryType' => __NAMESPACE__
		);

		$entry = parent::FromArray($data, $prefixes, $namespaces, $returnClass);
		return $entry;
		// if( is_array($data) ){
		// 	$entry = new self();
		// 	$fields = array_keys(get_object_vars($entry));
		// 	foreach ($data as $key => $value) {
		// 		if( in_array($key, $fields) ){
		// 			$entry->$key = $value;
		// 		}
		// 		if( $key == 'uid' ){
		// 			$entry->author = new User($key);
		// 		}
		// 	}
		// 	return $entry;
		// }
	}

	public function getID(){
		return $this->eid;
	}


	public function getTitle(){
		return $this->title;
	}


	public function getAuthor(){
		return $this->author;
	}


	public function getLink(){
		// TODO: page creation
		return $this->alias;
	}

	public function getTerms($type){

	}

	public function getType(){
		return $this->type;
	}

	public function getContent($short = false){
		// TODO: cut content
		return $this->content;
	}

	public function getStatus(){
		return $this->status;
	}

	public function isCommentsEnabled(){
		return $this->comments;
	}

	public function getLanguage(){
		// TODO: if lang is not set it is site lang.
		return $this->language;
	}

	public function getDate($fromCreation = false, $timestamp = false){
		$time = $fromCreation ? $this->created : $this->changed;
		if ( !$timestamp ){
			$format = Settings::Get('datetime_format');
			// If user have his own timezone we will use it.
			$timezone = User::Current()->getTimezone();
			if( empty($timezone) ){
				$timezone = Settings::Get('ucms_timezone');
			}
			$datetime = new \DateTime("@$time");
			// DateTime ignores $timezone parameter when created from timestamp, so
			// we have to set in explicitely.
			$datetime->setTimezone(new \DateTimeZone($timezone)); 
			return $datetime->format($format);
		}
		return $time;

	}

	public function isUserCanAccess($uid){
		if( User::Current()->can('manage entries') ){
			return true;	
		}

		if( $uid === $this->accessUid ){
			return (bool) $accessAllow;
		}

		if( $this->accessUid != 0 ){
			return !( (bool) $accessAllow );
		}
		$user = new User($uid);
		return $this->isGroupCanAccess($user->getGroup()->getID());
	}

	public function isGroupCanAccess($gid){
		// TODO: Admin access
		if( $gid === $this->accessGid ){
			return (bool) $accessAllow;
		}

		if( $this->accessGid != 0 ){
			return !( (bool) $accessAllow );	
		}
		return true;
	}

	public static function GetList($mode = self::MODE_BASE, $type = "", $sort = array(), $start = 0, $limit = self::LIMIT, $condition = "", $columns = array()){
		
	}
}
?>