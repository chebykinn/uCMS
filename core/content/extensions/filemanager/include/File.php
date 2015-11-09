<?php
namespace uCMS\Core\Extensions\FileManager;
use uCMS\Core\ORM\Model;
class File extends Model{
	const UPLOADS_PATH = 'content/uploads/';
	public function init(){
		$this->primaryKey('fid');
		$this->tableName('uploaded_files');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'user'));
	}

}
?>