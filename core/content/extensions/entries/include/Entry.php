<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Settings;
use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;
use uCMS\Core\Tools;
use uCMS\Core\Page;
use uCMS\Core\Cache;
class Entry extends Model{
	const DRAFT = 0;
	const PUBLISHED = 1;
	const PINNED = 2;
	const ACTION = "entry";

	public function init(){
		$this->primaryKey('eid');
		$this->tableName('entries');
		$this->hasMany("\\uCMS\\Core\\Extensions\\Entries\\Term", array('through' => 'term_taxonomy', 'bind' => 'terms'));
		$this->hasMany("\\uCMS\\Core\\Extensions\\Comments\\Comment", array('bind' => 'commentsList', 'key' => 'eid'));
		$this->belongsTo("\\uCMS\\Core\\Extensions\\Users\\User", array('bind' => 'author'));
		$this->belongsTo("\\uCMS\\Core\\Extensions\\Entries\\EntryType", array('bind' => 'entryType'));
	}

	public function getLink($row){
		if( (bool) Settings::Get("clean_url") ){
			return Page::FromAction($row->alias);
		}else{
			return Page::FromAction('entry', $row->eid);
		}
	}

	public function getDate($row, $fromCreation = false){
		$time = $fromCreation ? $row->created : $row->changed;
		
		return Tools::FormatTime($time);

	}

	public function getContent($row, $short = false){
		// TODO: cut content
		return $row->content;
	}

	public function isUserCanAccess($row, $uid){
		if( User::Current()->can('manage entries') ){
			return true;	
		}

		if( $uid === $row->accessUid ){
			return (bool) $accessAllow;
		}

		if( $row->accessUid != 0 ){
			return !( (bool) $accessAllow );
		}
		$user = new User($uid);
		return $row->isGroupCanAccess($user->getGroup()->getID());
	}

	public function isGroupCanAccess($row, $gid){
		// TODO: Admin access
		if( $gid === $row->accessGid ){
			return (bool) $accessAllow;
		}

		if( $row->accessGid != 0 ){
			return !( (bool) $accessAllow );	
		}
		return true;
	}
}
?>