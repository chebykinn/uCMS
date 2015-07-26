<?php
/**
* Base class for all extensions.
*
* This class is used as a top class for all extensions including themes.
*
* @since 2.0
*/
namespace uCMS\Core\Extensions;
use uCMS\Core\Debug;
use uCMS\Core\uCMS;
abstract class AbstractExtension{
	const INFO = 'extension.info';
	const PATH = 'content/extensions/';
	const CORE_PATH = 'core/content/extensions/';
	protected $name;
	protected $version;
	protected $coreVersion;
	protected $dependencies = NULL;
	protected $info;

	public function __construct($name){
		$this->name = $name;
		$this->loadInfo();
		$this->checkCoreVersion();
	}

	abstract protected function loadInfo();
	abstract protected function getRelativePath();
	

	protected function includeFile($file){
		if( file_exists($this->getFilePath($file)) ){
			include $this->getFilePath($file);
		}else{
			Debug::Log(tr("Failed to open file @s", $this->getFilePath($file)), Debug::LOG_ERROR);
		}
	}
	
	public function getFilePath($file){
		return ABSPATH.$this->getRelativePath()."$this->name/$file";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(self::INFO);
	}

	final protected function checkCoreVersion(){
		if( version_compare(uCMS::CORE_VERSION, $this->coreVersion, '<') ){
			Debug::Log(tr("Outdated core version @s", $this->name), Debug::LOG_ERROR);
			throw new \RuntimeException("Outdated core version");
		}
	}

	final public function getInfo($field){
		if( !empty($this->info[$field]) )
			return $this->info[$field];
		return "";
	}


	final public function getDependenciesList(){
		return $this->dependencies;
	}


	final public function getName(){
		return $this->name;
	}

	final public function getVersion(){
		return $this->version;
	}

}
?>