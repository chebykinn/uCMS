<?php
class Widget extends Extension{
	public function __construct($name){
		try{
			parent::__construct($name);
			$this->onLoad();
		}catch(Exception $e){
			p("Unable to load widget: @s", $name);
			echo $e->getMessage();
			log_add(tr("Unable to load widget: @s", $name), UC_LOG_ERROR);
		}
	}

	protected function getFilePath($file){
		return WIDGETS_PATH."$this->name/$file";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(WIDGET_INFO);
	}

	public function show(){
		$permissions = $this->getInfo("permissions");
		if( User::current()->can($permissions) || empty($permissions) ){
			$this->includeFile("index.php");
		}else{
			p("Access denied");
		}
	}
}
?>