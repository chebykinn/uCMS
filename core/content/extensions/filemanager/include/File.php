<?php
namespace uCMS\Core\Extensions\FileManager;
use uCMS\Core\ORM\Model;
class File extends Model{
	const CONTENT_PATH = 'content/';
	const UPLOADS_PATH = self::CONTENT_PATH.'uploads/';
	public function init(){
		$this->primaryKey('fid');
		$this->tableName('uploaded_files');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'user'));
	}

}
?>