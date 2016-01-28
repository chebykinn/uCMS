<?php
namespace uCMS\Core\Extensions\FileManager;
use uCMS\Core\ORM\Model;
use uCMS\Core\uCMS;
use uCMS\Core\Extensions\Users\User;
class File extends Model{
	const CONTENT_PATH = 'content/';
	const UPLOADS_PATH = self::CONTENT_PATH.'uploads/';
	const SYSTEM = 0;
	const UPLOADED_HIDDEN = 1;
	const UPLOADED_LISTED = 2;
	public function init(){
		$this->primaryKey('fid');
		$this->tableName('uploaded_files');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', ['bind' => 'user']);
	}

	public function getDate($row){
		return uCMS::FormatTime($row->changed);
	}

	public static function Exists($path){
		$path = ABSPATH.$path;

		return (file_exists($path) && is_file($path));
	}

	public function prepareFields($row){
		if( empty($row->name) ) return false;

		if( !isset($row->status) ){
			$row->status = self::UPLOADED_LISTED;
		}

		if( !isset($row->uid) ){
			$row->uid = 0;
			if( $row->status > self::SYSTEM ){
				$row->uid = User::Current()->uid;
			}
		}

		if( !isset($row->location) ){
			$row->location = self::UPLOADS_PATH;
		}

		if( strpos($row->location, "../") ){
			return false;
		}

		if( mb_substr($row->location, -1) != '/' ){
			$row->location .= '/';
		}

		if( !isset($row->type) ){
			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$row->type = $finfo->file($row->getPath());
		}

		if( !isset($row->size) ){
			$row->size = filesize($row->getPath());
		}
		
		if( !isset($row->changed) ){
			$row->changed = filemtime($row->getPath());
		}
	}

	public function getPath($row){
		return ABSPATH.$row->location.$row->name;
	}

	public function create($row){
		$result = $this->prepareFields($row);
		if( !$result ) return false;

		$result = parent::create($row);
		if( !$result ) return false;

		if( !self::Exists($row->location.$row->name) ){
			if( is_writable($row->getPath()) ){
				touch($row->getPath());
			}else return false;
		}

	}

	public function update($row){
		$result = $this->prepareFields($row);
		if( !$result ) return false;

		$result = parent::update($row);
		if( !$result ) return false;

		if( !self::Exists($row->location.$row->name) ){
			if( is_writable($row->getPath()) ){
				touch($row->getPath());
			}else return false;
		}
	}

	public function delete($row){
		if( empty($row->location) ) return false;
		if( empty($row->name) ) return false;
	}
}
?>