<?php
class Extension{
	protected $name;
	protected $version;
	protected $coreVersion;
	protected $dependencies = NULL;
	protected $loadAfter = NULL;
	protected $includes;
	protected $actions;
	protected $admin;
	protected $sidebarPosition;
	protected $adminPages = NULL;
	protected $info;

	public function __construct($name){
		$this->name = $name;
		$this->loadInfo();
		$this->checkCoreVersion();
		
	}

	public function onInstall(){
		Debug::Log(tr("@s installed", $this->name), UC_LOG_INFO);
	}

	public function onUninstall(){
		Debug::Log(tr("@s uninstalled", $this->name), UC_LOG_INFO);
	}

	public function onLoad(){
		if( is_array($this->includes) ){
			foreach ($this->includes as $include) {
				$this->includeFile($include);
			}
		}
		// Debug::Log(tr("@s loaded", $this->name), UC_LOG_INFO);
	}
	
	public function onShutdown(){
		
	}

	public function onAction($action){

	}

	public function onAdminAction($action){

	}

	public function getDependenciesList(){
		return $this->dependencies;
	}

	protected function includeFile($file){
		if( file_exists($this->getFilePath($file)) ){
			include $this->getFilePath($file);
		}else{
			Debug::Log(tr("Failed to open file @s", $this->getFilePath($file)), UC_LOG_ERROR);
		}
	}

	protected function getFilePath($file){
		return EXTENSIONS_PATH."$this->name/$file";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(EXTENSION_INFO);
	}

	private function loadInfo(){
		$encodedInfo = @file_get_contents($this->getExtensionInfoPath());
		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			Debug::Log(tr("Can't get extension information @s", $this->name), UC_LOG_ERROR);
			throw new InvalidArgumentException("Can't get extension information");
		}
		$this->version = $decodedInfo['version'];
		$this->coreVersion = $decodedInfo['coreVersion'];

		$this->dependencies = !empty($decodedInfo['dependencies']) ? $decodedInfo['dependencies'] : "";
		$this->loadAfter    = !empty($decodedInfo['loadAfter'])    ? $decodedInfo['loadAfter']    : "";
		$this->includes     = !empty($decodedInfo['includes'])     ? $decodedInfo['includes']     : "";
		$this->actions      = !empty($decodedInfo['actions'])      ? $decodedInfo['actions']      : "";
		$this->admin        = !empty($decodedInfo['admin'])        ? $decodedInfo['admin']        : array();
		$this->adminPages   = !empty($decodedInfo['adminPages'])   ? $decodedInfo['adminPages']   : "";
		$this->info         = !empty($decodedInfo['info'])         ? $decodedInfo['info']         : "";
		foreach ($this->admin as $key => &$item) {
			if( is_array($item) && count($item) == 2 ){ // if sidebar position is set
				if( empty($item[0]) ){
					$item[0] = $key;
					if( strpos($item[0], "separator" ) !== false ){
						$item[0] .= rand(0, 1000);
					}
				}
				$this->sidebarPosition[$item[0]] = $item[1];
				$item = $item[0]; 
			}else{
				if( empty($item) ){
					$item = $key;
					if( strpos($item, "separator" ) !== false ){
						$item .= rand(0, 1000);
					}
				}
			}
		}
	}

	private function checkCoreVersion(){
		if( version_compare(CORE_VERSION, $this->coreVersion, '<') ){
			Debug::Log(tr("Outdated core version @s", $this->name), UC_LOG_ERROR);
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

	public function getVersion(){
		return $this->version;
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

	public function getAdminSidebarPositions(){
		if( is_array($this->sidebarPosition) ){
			return $this->sidebarPosition;
		}
		return array();
	}

	public function getAdminPageFile($action){
		if( !empty($this->adminPages[$action]) && file_exists($this->getFilePath($this->adminPages[$action])) ){
			return $this->getFilePath($this->adminPages[$action]);
		}
		return "";
	}

	public function getIncludes(){
		if( is_array($this->includes) ){
			return $this->includes;
		}
		return array();
	}
	
}
?>