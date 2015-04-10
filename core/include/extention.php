<?php
class Extention{
	protected $name;
	protected $version;
	protected $coreVersion;
	protected $dependencies = NULL;
	protected $includes;
	protected $actions;
	protected $admin;
	protected $info;

	public function __construct($name){
		$this->name = $name;
		$this->loadInfo();
		$this->checkCoreVersion();
		
	}

	public function install(){
		log_add(tr("@s installed", $this->name), UC_LOG_INFO);
	}

	public function uninstall(){
		log_add(tr("@s uninstalled", $this->name), UC_LOG_INFO);
	}

	public function load(){
		if( is_array($this->includes) ){
			foreach ($this->includes as $include) {
				$this->includeFile($include);
			}
		}
		// log_add(tr("@s loaded", $this->name), UC_LOG_INFO);
	}
	
	public function doAction($action){

	}

	public function doAdminAction($action){

	}

	protected function includeFile($file){
		if( file_exists($this->getFilePath($file)) ){
			include $this->getFilePath($file);
		}else{
			log_add(tr("Failed to open file @s", $this->getFilePath($file)), UC_LOG_ERROR);
		}
	}

	protected function getFilePath($file){
		return EXTENTIONS_PATH."$this->name/$file";
	}

	protected function getExtentionInfoPath(){
		return $this->getFilePath(EXTENTION_INFO);
	}

	private function loadInfo(){
		$encodedInfo = @file_get_contents($this->getExtentionInfoPath());
		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			log_add(tr("Can't get extention information @s", $this->name), UC_LOG_ERROR);
			throw new InvalidArgumentException("Can't get extention information");
		}
		$this->version = $decodedInfo['version'];
		$this->coreVersion = $decodedInfo['coreVersion'];

		$this->dependencies = !empty($decodedInfo['dependencies']) ? $decodedInfo['dependencies'] : "";
		$this->includes     = !empty($decodedInfo['includes'])     ? $decodedInfo['includes']     : "";
		$this->actions      = !empty($decodedInfo['actions'])      ? $decodedInfo['actions']      : "";
		$this->admin        = !empty($decodedInfo['admin'])        ? $decodedInfo['admin']        : "";
		$this->info         = !empty($decodedInfo['info'])         ? $decodedInfo['info']         : "";
	}

	private function checkCoreVersion(){
		if((float)CORE_VERSION < (float)$this->coreVersion){
			log_add(tr("Outdated core version @s", $this->name), UC_LOG_ERROR);
			throw new RuntimeException("Outdated core version");
		}
	}

	public function getInfo($field){
		if( !empty($this->info[$field]) )
			return $this->info[$field];
		return "";
	}

	public function getName(){
		return $this->name;
	}

	public function getActions(){
		if( is_array($this->actions) ){
			return $this->actions;
		}
		return array();
	}

	public function getAdminActions(){
		if( is_array($this->admin) ){
			return array_values($this->admin);
		}
		return array();
	}

	public function getAdminSidebarItems(){
		if( is_array($this->admin) ){
			return $this->admin;
		}
		return array();
	}

	public function getIncludes(){
		if( is_array($this->includes) ){
			return $this->includes;
		}
		return array();
	}
	
}
?>