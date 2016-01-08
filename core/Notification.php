<?php
namespace uCMS\Core;
class Notification extends Object{
	private $type;
	private $message;
	private $page;
	private $amount;
	const SUCCESS = 'success';
	const WARNING = 'warning';
	const ERROR = 'error';
	private static $typeList = array(self::SUCCESS, self::WARNING, self::ERROR);

	public function __construct($message, $type = self::SUCCESS, $page = ''){
		if( !in_array($type, self::$typeList) ){
			$type = self::SUCCESS;
		}
		$this->message = $message;
		$this->type = $type;
		$this->page = $page;
	}

	public function add($uid = 0){
		if( $uid == 0 ){
			$data['message'] = $this->message;
			$data['type'] = $this->type;
			$data['page'] = $this->page;
			Session::GetCurrent()->push('ucms_notifications', $data);
		}

	}

	public static function AddType($name){
		if( !in_array($name, $typeList) ){
			$typeList[] = $name;
			return true;
		}
		return false;
	}

	public static function GetPending($name){
		// ?
	}

	public static function RemovePending($name){

	} 

	public static function ShowPending(){
		if( Session::GetCurrent()->have('ucms_notifications') ){
			$notifications = Session::GetCurrent()->get('ucms_notifications');
			if( !is_array($notifications) ) return;
			// var_dump($notifications);
			foreach ($notifications as $notification) {
				/**
				* @todo check for correct page
				*/
				echo '<div class="'.$notification['type'].'">'.$notification['message'].'</div>';
			}
			Session::GetCurrent()->delete('ucms_notifications');
		}
	}

	public static function ClearPending(){
		Session::GetCurrent()->delete('ucms_notifications');
	}
}
?>