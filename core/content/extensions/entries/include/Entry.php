<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Setting;
use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;
use uCMS\Core\Page;
use uCMS\Core\Cache;
use uCMS\Core\uCMS;
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
		if( (bool) Setting::Get("clean_url") ){
			return Page::FromAction($row->alias);
		}else{
			return Page::FromAction('entry', $row->eid);
		}
	}

	public function getDate($row, $fromCreation = false){
		$time = $fromCreation ? $row->created : $row->changed;
		
		return uCMS::FormatTime($time);

	}

	public function getContent($row, $short = false){
		$captionRegex = "/@-more-@(((.*)-@)?(.*)-@)?/";
		$class = "more-link";
		$caption = $this->tr('Continue reading');
		if( preg_match("$captionRegex", $row->content, $matches) ){
			$tag = $matches[0];
			if( $short ){
				$contentData = explode($tag, $row->content);
				$content = $contentData[0];
	
				if( !empty($matches[3]) ){
					$class = $matches[3];
				}
	
				if( !empty($matches[4]) ){
					$caption = $matches[4];
				}

				$link = '<a class="'.$class.'" href="'.$row->getLink().'" title="'.$caption.'">'.$caption.'</a>';
				$content .= $link;
			}else{
				$content = str_replace($tag, "", $row->content);
			}
			return $content;
		}
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

	public function getEditLink($row){
		$user = User::Current();
		$editLink = Page::ControlPanel('entries/edit/'.$row->eid);
		return $editLink;
	}

	protected function prepareFields($row){
		if( empty($row->title) || empty($row->content) ){
			return false;
		}

		if( empty($row->alias) ){
			$row->alias = $row->title;
		}

		if( empty($row->type) ){
			$row->type = EntryType::ARTICLE;
		}

		if( empty($row->uid) ){
			$user = User::Current();
			$row->uid = $user->uid;
		}

		if( empty($row->created) ){
			$row->created = time();
		}

		if( empty($row->changed) ){
			$row->changed = time();
		}
		return true;
	}

	public function create($row){
		$result = parent::create($row);
		if( $result ){
			Setting::Increment('entries_amount', $this);
		}
		return $result;
	}

	public function delete($row){
		$result = parent::delete($row);
		if( $result ){
			Setting::Decrement('entries_amount', $this);
		}
		return $result;
	}
}
?>
