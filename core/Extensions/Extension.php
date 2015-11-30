<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\Debug;
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Notification;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Database\Query;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\uCMS;
use uCMS\Core\Session;
use uCMS\Core\Installer;
class Extension extends AbstractExtension implements ExtensionInterface{
	const INFO = 'extension.info';
	const PATH = 'content/extensions/';
	const CORE_PATH = 'core/content/extensions/';

	private $loadAfter = NULL;
	private $includes = [];
	private $actions = [];
	private $admin = [];
	private $sidebarPosition;
	private $adminPages = NULL;
	private static $list = [];
	private static $usedActions;
	private static $usedAdminActions;
	private static $defaultList;

	final public function __construct($name){
		$this->name = $name;
		$this->loadInfo();

		$this->checkCoreVersion();
		if( is_array($this->includes) ){
			foreach ($this->includes as $include) {
				$this->includeFile($include);
			}
		}
	}

	public function onLoad(){

	}

	public function onInstall($stage){
		if( $stage === Installer::CHECK_STAGE ){
			return $this->checkStage();
		}

		if( $stage === Installer::UPDATE_STAGE ){
			return $this->updateStage();
		}

		if( $stage === Installer::PREPARE_STAGE ){
			return $this->prepareStage();
		}

		if( $stage === Installer::PRINT_STAGE ){
			$this->printStage();
		}
	}

	public function onUninstall(){

	}

	public function onAction($action){

	}

	public function onAdminAction($action){

	}

	public function onShutdown(){

	}

	protected function loadInfo(){

		$encodedInfo = @file_get_contents($this->getExtensionInfoPath());

		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			throw new \InvalidArgumentException("Can't get extension information");
		}
		$this->version = $decodedInfo['version'];
		$this->coreVersion = $decodedInfo['coreVersion'];

		$this->dependencies = !empty($decodedInfo['dependencies']) ? $decodedInfo['dependencies'] : [];
		$this->info         = !empty($decodedInfo['info'])         ? $decodedInfo['info']         : [];
		$this->loadAfter    = !empty($decodedInfo['loadAfter'])    ? $decodedInfo['loadAfter']    : [];
		$this->includes     = !empty($decodedInfo['includes'])     ? $decodedInfo['includes']     : [];
		$this->actions      = !empty($decodedInfo['actions'])      ? $decodedInfo['actions']      : [];
		$this->admin        = !empty($decodedInfo['admin'])        ? $decodedInfo['admin']        : [];
		$this->adminPages   = !empty($decodedInfo['adminPages'])   ? $decodedInfo['adminPages']   : [];
		$this->settings     = !empty($decodedInfo['settings'])     ? $decodedInfo['settings']     : [];
		$this->permissions  = !empty($decodedInfo['permissions'])  ? $decodedInfo['permissions']  : [];
		$this->blocks       = !empty($decodedInfo['blocks'])       ? $decodedInfo['blocks']       : [];
		$separatorIndex = 1;
		$prevAction = Page::INDEX_ACTION;
		$separator = false;
		foreach ($this->admin as $key => &$item) {
			$separator = false;
			if( is_array($item) && count($item) == 2 ){ // if sidebar position is set
				$action = $item[0];
				$after  = $item[1];
				$weight = -1;
			}else{
				$action = $item;
				$after  = $prevAction;
				$weight = -2;
			}
			if( empty($action) ){
				$action = $key;
				if( strpos($action, "separator" ) !== false ){
					$action .= ++$separatorIndex;
					$separator = true;
				}
			}

			if( $this->name === 'admin' && $action === Page::INDEX_ACTION ){
				$weight = 0;
			}

			if( strpos($action, ControlPanel::SETTINGS_ACTION.'/') !== false ){
				$after = ControlPanel::SETTINGS_ACTION;
			}
			$item = array('name' => $key, 'action' => $action, 'after' => $after, 'weight' => $weight);
			if( !$separator ){
				$prevAction = $action;
			}
		}
	}

	final public function getActions(){
		return $this->actions;
	}

	final public function getAdminActions(){
		$actions = [];
		foreach ($this->admin as $key => $item) {
			$actions[] = $item['action'];
		}
		return $actions;
	}

	final public function getAdminSidebarItems(){
		return $this->admin;
	}

	final public function getAdminPageFile($action){
		if( !empty($this->adminPages[$action]) && file_exists($this->getFilePath($this->adminPages[$action])) ){
			return $this->getFilePath($this->adminPages[$action]);
		}
		return "";
	}

	final public function getIncludes(){
		return $this->includes;
	}

	final public function getTables(){
		if( is_array($this->getInfo('tables')) ){
			return $this->getInfo('tables');
		}
		return [];
	}

	final public function getDatabase(){
		$database = $this->getInfo('database');
		return $database;
	}

	final protected function getRelativePath(){
		return ExtensionHandler::IsDefault($this->name) ? ExtensionHandler::CORE_PATH : ExtensionHandler::PATH;
	}

	protected function checkStage(){
		if( !empty($this->settings) && is_array($this->settings) ){
			Settings::AddMultiple($this->settings);
		}
		$tables = $this->getInfo('tables');

		if( !empty($tables) && is_array($tables) ){
			$missing = $this->checkTables($tables);
			if( !empty($missing) ){
				Session::GetCurrent()->set($this->name.'_missing_tables', serialize($missing));
				Installer::GetInstance()->sendRequest($this->name.'_need_tables');
				return true;
			}
		}

		$tablesToFill = $this->getInfo('tablesToFill');
		if( !empty($tablesToFill) && is_array($tablesToFill) ){
			$emptyTables = [];
			foreach ($tablesToFill as $table) {
				$query = new Query('{'.$table.'}');
				$count = $query->countRows()->execute();
				if( $count === 0 ){ // TODO: min size
					$emptyTables[] = $table;
				}
			}

			if( !empty($emptyTables) ){
				Session::GetCurrent()->set($this->name.'_empty_tables', serialize($emptyTables));
				Installer::GetInstance()->sendRequest($this->name.'_fill_tables');
				return true;
			}
		}
		if( !empty($this->permissions) ){
			$amount = count($this->permissions);
			foreach ($this->permissions as $permission => $gids) {
				if( is_array($gids) ){
					$amount += count($gids)-1;
				}
			}

			$havePermissions = $this->checkPermissions($amount);
			if( !$havePermissions ){
				Installer::GetInstance()->sendRequest($this->name.'_need_permissions');
				return true;
			}
		}

		return false;
	}

	protected function prepareStage(){
		
		if( Installer::GetInstance()->isRequested($this->name.'_need_tables') ){
			$needForm = false;
			$schemas = $this->getSchemas();
			$missingTables = unserialize(Session::GetCurrent()->get($this->name.'_missing_tables'));
			Session::GetCurrent()->delete($this->name.'_missing_tables');

			$tablesToFill = is_array($this->getInfo('tablesToFill')) ? $this->getInfo('tablesToFill') : [];
			if( !is_array($missingTables) ) return ExtensionHandler::DONE_INSTALL;
			foreach ($missingTables as $table) {
				if( !isset($schemas[$table]) ) continue;
				$query = new Query('{'.$table.'}');
				$query->createTable($schemas[$table])->execute();

				// If we need to fill the table, we call method to fill it
				// or to notify us, that installer needs form to fill it
				if( in_array($table, $tablesToFill) ){
					$needForm = $this->fillTable($table);
				}
			}

			if( $needForm ){
				return ExtensionHandler::NEED_USER_INPUT;
			}

			return ExtensionHandler::DONE_INSTALL;
		}

		if( Installer::GetInstance()->isRequested($this->name.'_fill_tables') ){

			$needForm = false;
			$emptyTables = unserialize(Session::GetCurrent()->get($this->name.'_empty_tables'));
			Session::GetCurrent()->delete($this->name.'_empty_tables');
			if( !is_array($emptyTables) ) return ExtensionHandler::DONE_INSTALL;
			foreach ($emptyTables as $table) {
				$needForm = $this->fillTable($table);
			}

			if( $needForm ){
				return ExtensionHandler::NEED_USER_INPUT;
			}
			return ExtensionHandler::DONE_INSTALL;
		}

		if( Installer::GetInstance()->isRequested($this->name.'_need_permissions') ){
			$this->addPermissions();
		}
		return ExtensionHandler::DONE_INSTALL;
	}

	protected function updateStage(){
		return false;
	}

	protected function printStage(){

	}

	protected function getSchemas(){
		return [];
	}

	protected function fillTable($table){
		return false;
	}

	protected function addPermissions(){
		$rows = [];
		$owner = $this->name;
		foreach ($this->permissions as $permission => $gids) {
			if( is_array($gids) ){
				foreach ($gids as $gid) {
					$rows[] = [$gid, $permission, $owner];
				}
			}else{
				$rows[] = [$gids, $permission, $owner];
			}
		}
		$query = new Query('{group_permissions}');
		$query->insert(['gid', 'name', 'owner'], $rows)->execute();
	}

	protected function checkTables(array $tables){
		$missing = [];
		foreach ($tables as $table) {
			$query = new Query('{'.$table.'}');
			$exists = $query->tableExists();
			if( !$exists ){
				$missing[] = $table;
			}
		}
		return $missing;
	}

	protected function checkPermissions($amount){
		$query = new Query('{group_permissions}');
		$owner = $this->name;
		$count = $query->select('1')->condition('owner', '=', $owner)->execute('count');
		return ($count >= $amount);
	}
}
?>