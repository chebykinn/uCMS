<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Settings;
class Entry{
	const DRAFT = 0;
	const PUBLISHED = 1;
	const PINNED = 2;
	var $eid;
	var $user;
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

	public static function FromArray($data){
		if( is_array($data) ){
			$entry = new self();
			$fields = array_keys(get_object_vars($entry));
			foreach ($data as $key => $value) {
				if( in_array($key, $fields) ){
					$entry->$key = $value;
				}
				if( $key == 'uid' ){
					$entry->user = new User($key);
				}
			}
			return $entry;
		}
	}

	public function getID(){
		return $this->eid;
	}


	public function getTitle(){
		return $this->title;
	}


	public function getAuthor(){
		// TODO: user object
		return $user;
	}


	public function getLink(){
		// TODO: page creation
		return $this->alias;
	}

	public function getTerms($type){

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

	public static function GetList($type, $start, $limit){

	}
}
?>