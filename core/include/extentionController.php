<?php
class ExtentionController{
	private $list;
	private $usedActions;
	private $usedAdminActions;

	public function create($extentions){
		$this->list = array();
		$this->usedActions = array();
		$this->usedAdminActions = array();
		if( !is_array($extentions) ){
			$extentions = array($extentions);
		}
		foreach ($extentions as $extention) {
			if( file_exists(EXTENTIONS_PATH.$extention.'/extention.php') ){
				include EXTENTIONS_PATH.$extention.'/extention.php';
				if( class_exists($extention) ){
					try{
						$this->list[$extention] = new $extention($extention);
					}catch(InvalidArgumentException $e){
						p("[@s]: ".$e->getMessage(), $extention);
					}catch(RuntimeException $e){
						p("[@s]: ".$e->getMessage(), $extention);
					}
					
					
				}
			}
		}
	}

	public function load(){
		if( is_array($this->list) ){
			$extentionActions = $extentionAdminActions = array();
			foreach ($this->list as $name => $extention) {
				if( is_object($extention) ){
					$extention->load();
					
					$extentionActions = is_array($extention->getActions()) ? $extention->getActions() : array();
					$extentionAdminActions = is_array($extention->getAdminActions()) ? $extention->getAdminActions() : array();
					$this->usedActions = array_merge($this->usedActions, $extentionActions);
					$this->usedAdminActions = array_merge($this->usedAdminActions, $extentionAdminActions);
				}
			}
			$this->usedActions = array_unique($this->usedActions);
			$this->usedAdminActions = array_unique($this->usedAdminActions);
		}
	}

	public function loadOnAction($action){
		if( is_array($this->list) ){
			$count = 0;
			$templateData = "";
			foreach ($this->list as $name => $extention) {
				if( !in_array($action, $this->usedActions) ) $action = OTHER_ACTION;
				if( is_object($extention) && in_array($action, $extention->getActions())){
					$templateData = $extention->doAction($action);
					$count++;
				}
			}
			if($count == 0) return "";
			return $templateData;
		}
		return "";
	}

	public function loadOnAdminAction($action){
		if( is_array($this->list) ){
			$title = "";
			foreach ($this->list as $name => $extention) {
				if( is_object($extention) && in_array($action, $extention->getAdminActions()) ){
					$title = $extention->doAdminAction($action);
				}
			}
			if( !empty($title) ) $title = ' :: '.$title;
		}
		return array( "template" => ADMIN_ACTION, "title" => tr("μCMS Control Panel$title") );
	}

	public function getUsedAdminActions(){
		return $this->usedAdminActions;
	}

	public function getUsedActions(){
		return $this->usedActions;
	}

	public function get($name){
		if( !empty($this->list[$name]) && is_object($this->list[$name]) ){
			return $this->list[$name];
		}
		return '';
	}

	public function getLoadedExtentions(){
		$names = array();
		foreach ($this->list as $name => $extention) {
			if( is_object($extention) ){
				$names[] = $name;
			}
		}
		return $names;
	}
}
?>