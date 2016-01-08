<?php
namespace uCMS\Core;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\Database\Query;
use uCMS\Core\Localization\Language;
use uCMS\Core\Extensions\FileManager\File;

class Installer extends Object{
	private $stageCallback = "welcome";
	private $currentStage = "";
	const LANGUAGE_STAGE = 'language';
	const WELCOME_STAGE = 'welcome';
	const CONFIG_STAGE = 'config';
	const CONNECT_ERROR_STAGE = 'connectionError';
	const CHECK_STAGE = 'check';
	const PREPARE_STAGE = 'prepare';
	const PRINT_STAGE = 'print';
	const TABLES_STAGE = 'tables';
	const EXTENSIONS_STAGE = 'extensions';
	const SITEINFO_STAGE = 'siteInformation';
	const UPDATE_STAGE = 'update';
	const FINE_STAGE = 'fine';
	const DONE_STAGE = 'done';
	/**
	* @var Installer $instance Contains current instance of installer if running.
	*/
	private static $instance = null;

	/**
	* Singleton method that provides access to the current instance of Installer.
	*
	* @throws RuntimeException if installation is not running.
	* @param none
	* @return Installer Current instance of Installer.
	*/
	public static function GetInstance(){
		if ( !is_null( self::$instance ) ){
			return self::$instance;
		}else{
			throw new \RuntimeException("µCMS Installer is not running.");
		}
	}

	/**
	* Check if installer is running.
	*
	* This method checks if installer is running.
	*
	* @since 2.0
	* @param none
	* @return bool True if running, false otherwise.
	*/
	public static function IsRunning(){
		return !is_null( self::$instance );
	}

	/**
	* Start installer.
	*
	* Installer constructor checks current params, given through Page data, and sets current stage, after certain checks.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	public function __construct() {
		// Detect current stage
		$this->currentStage = Page::GetCurrent()->getActionData();
		
		$this->checkStage();

		self::$instance = $this;
	}

	/**
	* Check current stage.
	*
	* This method checks connection to database, and if it isn't established
	* it will move stage to creating config file. Otherwise, it will run check stage.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	private function checkStage(){
		// First we check configuration file
		$connected = is_object(DatabaseConnection::GetDefault()) ? DatabaseConnection::GetDefault()->isConnected() : false;

		if (!$connected) {
			if( !$this->isLanguageSet() ){
				$this->setStage(self::LANGUAGE_STAGE);
			}else{
				if( empty($this->currentStage) ){
					if( $this->isConfigFileExists() ){
						$this->setStage(self::CONNECT_ERROR_STAGE);
					}else{
						$this->setStage(self::WELCOME_STAGE);
					}
				}
			}
		}else{
			$class = new \ReflectionClass(__CLASS__);
			$stages = $class->getConstants();
			if( !in_array($this->currentStage, $stages) ){
				$this->setStage(self::CHECK_STAGE);
			}
		}
	}

	/**
	* Set installer stage.
	*
	* Set stage without changing page.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	public function setStage($stage){
		if( $this->currentStage !== $stage && !empty($stage) ){
			$this->currentStage = $stage;
		}
	}

	/**
	* Switch installer stage.
	*
	* Move to another installer page.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	public function switchStage($stage){
		if( $this->currentStage !== $stage && !empty($stage) ){
			$this->setStage($stage);
			$page = Page::Install($stage);
			$page->go();
		}
	}

	/**
	* Get current stage.
	*
	* Get stage of running installation.
	*
	* @since 2.0
	* @param none
	* @return string Installer stage
	*/
	public function getCurrentStage(){
		return $this->currentStage;
	}

	/**
	* Check if language is set.
	*
	* This method will check language preference, when there is no database settings.
	*
	* @since 2.0
	* @param none
	* @return bool State of language preference.
	*/
	public function isLanguageSet(){
		$language = Session::GetCurrent()->get('language');
		return !empty($language);
	}

	/**
	* Check config file.
	*
	* Check if config file exists. This method should check config in base directory and in directory above.
	*
	* @since 2.0
	* @param none
	* @return bool True if exists, false if not.
	*/
	public function isConfigFileExists(){
		$configFirst = ABSPATH.'config.php';
		$configUpper = dirname(ABSPATH).'/config.php';
	
		return ( (file_exists($configFirst) && is_file($configFirst))
			  || (file_exists($configUpper) && is_file($configUpper)) );
	}

	/**
	* Set title for stage.
	*
	* Set title for current installer stage.
	*
	* @since 2.0
	* @param string $newTitle Title to set.
	* @return void
	*/
	public function setTitle($newTitle = ""){
		$delimeter = " :: ";
		if( empty($newTitle) ) $delimeter = "";
		$title = $this->tr('µCMS Installation').$delimeter.$newTitle;
		Theme::GetCurrent()->setTitle($title);
	}

	/**
	* Run installer.
	*
	* This method start installation by loading installer theme, and running stage specific checks.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	public function run(){
		if( Page::GetCurrent()->getAction() != Page::INSTALL_ACTION ){
			$installPage = Page::Install($this->currentStage);
			$installPage->go();
		}else{
			try{
				Theme::SetCurrent('install');
			}catch(\InvalidArgumentException $e){
				$this->p("[@s]: ".$e->getMessage(), 'Installer');
			}catch(\RuntimeException $e){
				$this->p("[@s]: ".$e->getMessage(), 'Installer');
			}
			$this->prepareStage();
			// Load theme without preparing environment variables
			Theme::GetCurrent()->load(false);
			
		}
		exit;
	}

	private function prepareStage(){
		$this->stageCallback = "{$this->currentStage}Stage";
		$isPosted = (isset($_POST['stage']) && $_POST['stage'] === $this->currentStage);
		$nextStage = "";
		$this->setTitle();
		switch ($this->currentStage) {
			case self::LANGUAGE_STAGE:
				$this->setTitle($this->tr('Select Language'));
				if( $isPosted ){
					$language = isset($_POST['language']) ? $_POST['language'] : 'en_US';
					Session::GetCurrent()->set('language', $language);
					$nextStage = self::WELCOME_STAGE;
				}
			break;

			case self::WELCOME_STAGE:
				$this->setTitle($this->tr('Welcome!'));
				if( $isPosted ){
					$nextStage = self::CONFIG_STAGE;
				}
			break;

			case self::CONFIG_STAGE:
				$nextStage = $this->prepareConfigStage($isPosted);
				
			break;

			case self::CONNECT_ERROR_STAGE:
				$this->setTitle($this->tr('Error Connecting To Database'));
				if( $isPosted ){
					$nextStage = self::CONFIG_STAGE;
				}
			break;

			case self::TABLES_STAGE:
				$nextStage = $this->prepareTablesStage($isPosted);
			break;

			case self::CHECK_STAGE:
				$this->prepareCheckStage();
			break;

			case self::SITEINFO_STAGE:
				$this->setTitle($this->tr('Site Information'));
				if( $isPosted ){
					$nextStage = $this->checkSettings($isPosted);
				}
			break;

			case self::EXTENSIONS_STAGE:
				ExtensionHandler::Install(self::PREPARE_STAGE);
			break;

			case self::UPDATE_STAGE:
				$this->prepareUpdateStage();
			break;

			case self::FINE_STAGE:
				Setting::UpdateValue(Setting::UCMS_MAINTENANCE, '0', $this);
				$this->setTitle($this->tr('Everything\'s Fine'));
			break;

			case self::DONE_STAGE:
				Setting::UpdateValue(Setting::UCMS_MAINTENANCE, '0', $this);
				$this->setTitle($this->tr('Well done!'));
			break;
		}
		if( $isPosted ){
			$this->switchStage($nextStage);
		} 
	}

	private function prepareConfigStage($isPosted){
		$this->setTitle($this->tr('Database Connection Configuration'));
		$server = !empty($_POST['server']) ? $_POST['server'] : 'localhost';
		$nextStage = "";
		if( $isPosted ){
			$port     = explode(":", $server);
			$dbPort   = !empty($port[1]) ? (int) $port[1] : "";
			if( !empty($dbPort) ){
				$server = $port[0];
			}
			$login    = !empty($_POST['user']) ? $_POST['user'] : "root";
			$password = !empty($_POST['password']) ? $_POST['password'] : "";
			$dbName   = !empty($_POST['name']) ? $_POST['name'] : "ucms";
			$prefix   = !empty($_POST['prefix']) ? $_POST['prefix'] : "uc_";
			try{
				$check = new DatabaseConnection(
					$server,
					$login,
					$password,
					$dbName,
					$dbPort,
					$prefix,
					$ucmsName
				);
				$fields = ["%name%", "%server%", "%port%", "%user%", "%password%", "%prefix%", "%salt%"];
				$values = [$dbName, $server, $dbPort, $login, $password, $prefix, uCMS::GenerateHash()];
				$config = file_get_contents(ABSPATH.uCMS::CONFIG_SAMPLE);
				$config = str_replace($fields, $values, $config);
				$file = @fopen(ABSPATH.uCMS::CONFIG_FILE, "w+");
				if( $file === false){
					$nextStage = self::CONNECT_ERROR_STAGE;
				}else{
					// Data is correct now we have to create config.php
					fprintf($file, '%s', $config);
					fclose($file);
					$nextStage = self::CHECK_STAGE;
				}
			}catch(\PDOException $e){
				$nextStage = self::CONNECT_ERROR_STAGE;
			}
		}
		return $nextStage;
	}

	private function prepareTablesStage($isPosted){
		$this->setTitle($this->tr('Creating Tables'));
		$nextStage = "";
		if( $isPosted ){
			// TODO: Update tables
			$database = DatabaseConnection::GetDefault();

			if( is_null($database) ) $nextStage = self::CONNECT_ERROR_STAGE;
			else{
				$tables = $database->checkDefaultTables();
				$schemas = $this->getDefaultSchemas();
				foreach ($tables as $table => $exists) {
					if( !$exists ){
						$createQuery = new Query('{'.$table.'}');
						$createQuery->createTable($schemas[$table]);
						$createQuery->execute();
					}
				}
				$nextStage = self::CHECK_STAGE;
				$this->sendRequest('installed');
			}
		}
		return $nextStage;
	}

	private function prepareCheckStage(){
		$this->setTitle($this->tr('Checking State...'));
		// If update process was started
		if( isset($_POST['update']) && isset($_POST['action']) ){
			Session::GetCurrent()->set('update-action', $_POST['action']);
			if( isset($_POST['package']) ){
				Session::GetCurrent()->set('update-package', $_POST['package']);
			}
			$nextStage = self::UPDATE_STAGE;
		}else{
			// Check core tables
			$database = DatabaseConnection::GetDefault();

			$tables = $database->checkDefaultTables();
			if( in_array(false, $tables) ){
				$nextStage = self::TABLES_STAGE;
			}else{
				// Check settings table
				$nextStage = $this->checkSettings(false);
				// Load extensions checks
				if( empty($nextStage) ){
					$needInstall = ExtensionHandler::CheckInstall();

					if( $needInstall ){
						$nextStage = self::EXTENSIONS_STAGE;
						$this->sendRequest('installed');
					}else{
						if( $this->isRequested('updated') ){
							$nextStage = self::UPDATE_STAGE;
						}else if( $this->isRequested('installed') ){
							$nextStage = self::DONE_STAGE;
						}else{
							$nextStage = self::FINE_STAGE;
						}
					}
				}
			}
		}

		$this->switchStage($nextStage);
	}

	private function prepareUpdateStage(){
		$this->setTitle($this->tr('Update in Process'));
		$action = Session::GetCurrent()->get('update-action');
		$package = Session::GetCurrent()->get('update-package');
		Session::GetCurrent()->delete('update-action');
		Session::GetCurrent()->delete('update-package');
		$currentVersion = uCMS::CORE_VERSION;
		$installPath = ABSPATH;
		$backupPath = ABSPATH.File::UPLOADS_PATH;
		$backupName = 'ucms';
		if( empty($package) ){
			// If installation wasn't started from file, we should download package
			$version = uCMS::CORE_VERSION;
			switch ($action) {
				case 'update':
					$version = uCMS::GetLatestVersion();
				break;
				
				case 'reinstall':
					$version = uCMS::CORE_VERSION;
				break;
			}

			$result = uCMS::DownloadPackage($version);

			if( $result == uCMS::ERR_NOT_FOUND ){
				$error = new Notification($this->tr('Error: Unable to get update package'), Notification::ERROR);
				$error->add();
				$back = Page::ControlPanel('update');
				$back->go();
			}
			$package = ABSPATH.File::UPLOADS_PATH.'update.zip';
		}
		//	Backup previous files => Download package => Unpack files => Run install check with new version
		Setting::UpdateValue(Setting::UCMS_MAINTENANCE, '1', $this);
		$this->createBackup($backupPath, $backupName, $currentVersion);
		$result = $this->extractUpdate($installPath, $package);
		if( $result != uCMS::SUCCESS ){
			switch ($result) {
				case uCMS::ERR_NO_PERMISSIONS:
					$error = new Notification($this->tr('Error: Unable to write to: @s', $installPath), Notification::ERROR);
				break;

				case uCMS::ERR_INVALID_PACKAGE:
					$error = new Notification($this->tr('Error: Invalid package provided: @s', $package), Notification::ERROR);
				break;

				case uCMS::ERR_NO_UPDATE_PACKAGE:
					$error = new Notification($this->tr('Error: Package not found: @s', $package), Notification::ERROR);
				break;
			}
			$error->add();
			$back = Page::ControlPanel('update');
			$back->go();
		}
		$this->sendRequest('updated');
		Setting::UpdateValue(Setting::UCMS_MAINTENANCE, '0', $this);
	}

	public function printStage(){
		print "<h2>".Theme::GetCurrent()->getTitle()."</h2>";
		$this->stageCallback();
	}

	public function installForm($name, $button = ''){
		if ( empty($button) ){
			$button = $this->tr('Next');
		}
		$form = new Form($name, Page::Install($this->currentStage), $button);
		$form->addHiddenField("stage", $this->currentStage);
		return $form;
	}

	public function sendRequest($name){
		Session::GetCurrent()->set("installer_request_$name", true);
	}

	public function isRequested($name){
		if( Session::GetCurrent()->have("installer_request_$name") ){
			Session::GetCurrent()->delete("installer_request_$name");
			return true;
		}
		return false;
	}

	private function languageStage(){
		$form = $this->installForm('language-form', 'Continue');
		$languages = Language::GetList();
		$form->addSelectField($languages, "languages", "", "", "en_US", 16);
		$form->render();
	}

	private function welcomeStage(){
		print '<p>';
		$this->p('Welcome to μCMS! Before you can enjoy your site, we need some information on database, where all data will be stored.<br>After a few steps of configuration you\'ll be ready to use your site.');
		print '</p>';

		$form = $this->installForm('welcome-form', $this->tr('Let\'s do this!'));
		$form->render();
	}

	private function configStage(){
		$form = $this->installForm('config-form');
		$form->addField('server', 'text', $this->tr('Database Server:'), $this->tr('Most likely it\'s "localhost".'), 'localhost', $this->tr('server'));
		$form->addField('user', 'text', $this->tr('User:'), $this->tr('Login of database user, provided by website hosting.'), 'root', $this->tr('user'));
		$form->addField('password', 'text', $this->tr('Password:'), $this->tr('Password of database user, provided by website hosting.'), '', $this->tr('password'), false);
		$form->addField('name', 'text', $this->tr('Database name:'), $this->tr('Name of database, where you want to install μCMS.'), 'ucms', $this->tr('name'));
		$form->addField('prefix', 'text', $this->tr('Tables prefix:'), $this->tr('You can change it to install different μCMS sites in one database.'), 'uc_', $this->tr('prefix'));
		$form->render();
		print '<p>';
		$this->p("<b>Note:</b> If config.php will not be created you can use config-manual.php to create it yourself.");
		print '</p>';
	}

	private function connectionErrorStage(){
		print '<p>';
		$this->p("An error occurred while connecting to database, this means either you have provided incorrect username and password, or we can't reach your database server.<br>Please make sure that you're entered correct data and try again. If this error still occurs, contact server administrator.");
		print '</p>';

		$form = $this->installForm('error-form', $this->tr('Try again'));
		$form->render();
	}

	private function tablesStage(){
		print '<p>';
		$this->p('There is lack of our tables in your database, need to create some!');
		print '</p>';
		$form = $this->installForm('allow-tables', $this->tr('Alrighty then!'));
		$form->render();
	}

	private function siteInformationStage(){
		print '<p>';
		$this->p('Just a few more steps to go!');

		$form = $this->installForm('info-form');
		$form->addField('name', 'text', $this->tr('Site Name:'), $this->tr('Give yor site a name, like: "Bob\'s site".'), 'Site on μCMS', $this->tr('Name'));
		$form->addField('description', 'text', $this->tr('Site Description:'), $this->tr('Describe your site, like: "Cool site about me!".'), 'The site indeed', $this->tr('Description'));
		$form->addField('title', 'text', $this->tr('Site Title:'), $this->tr('Displayed at the top of the browser\'s page,<br> for instance: "Bob\'s site is the best site in the world!".'), 'The Best Site on μCMS', $this->tr('Title'));
		$form->render();
		print '</p>';
	}

	private function extensionsStage(){
		ExtensionHandler::Install(self::PRINT_STAGE);
	}

	private function checkSettings($isPosted){
		// TODO: Update
		$settingsAmount = (new Setting())->count();
		if( $settingsAmount < Setting::DEFAULT_AMOUNT ){
			$name = Setting::Get(Setting::SITE_NAME);
			$description = Setting::Get(Setting::SITE_DESCRIPTION);
			$title = Setting::Get(Setting::SITE_TITLE);
			$domain = Setting::Get(Setting::SITE_DOMAIN);
			$ucmsDir = Setting::Get(Setting::UCMS_DIR);

			$newName  = '';
			$newDesc  = '';
			$newTitle = '';
			if( empty($name) || empty($description) || empty($title) ){
				if( !$isPosted ) return self::SITEINFO_STAGE;
				$newName  = !empty($_POST['name'])        ? $_POST['name']        : $this->tr('Site on μCMS');
				$newDesc  = !empty($_POST['description']) ? $_POST['description'] : $this->tr('The site indeed');
				$newTitle = !empty($_POST['title'])       ? $_POST['title']       : $this->tr('The Best Site on μCMS');
			}
			$newDomain = Page::GetCurrent()->getHost();
			$newUcmsDir = Page::GetCurrent()->getPath();
			$this->addSettings($newName, $newDesc, $newTitle, $newDomain, $newUcmsDir);
			return self::EXTENSIONS_STAGE;
		}
		if( !$isPosted ) return '';
		return self::CHECK_STAGE;
	}

	private function updateStage(){
		$this->switchStage(self::CHECK_STAGE);

	}

	private function doneStage(){
		print '<p>';
		$this->p('μCMS was successfully installed, so go and explore your new site.');
		print '</p>';
		$homePage = Page::Home();
		print '<br><a class="button" href="'.$homePage.'">'.$this->tr('To the home page').'</a>';
	}

	private function fineStage(){
		print '<p>';
		$this->p('μCMS is already installed and everything is working flawlessly (according to our checks).<br> To reinstall μCMS please delete our tables or configuration file.');
		print '</p>';
		$homePage = Page::Home();
		print '<br><a class="button" href="'.$homePage.'">'.$this->tr('Back to the home page').'</a>';
	}

	public function __call($method, $args){
		if( method_exists($this, $this->stageCallback) ){
			return call_user_func_array(array($this, $this->stageCallback), $args);
		}
	}

	private function createBackup($path, $name, $version){
		$isBackupEnabled = (bool)Setting::Get(Setting::DO_UPDATE_BACKUP);
		if( $isBackupEnabled ){ // Backup all files except uploads directory
			$name = mb_strtolower(str_replace(" ", "-", $name));
			$version = mb_strtolower(str_replace(" ", "-", $version));
			$backup = ABSPATH.File::UPLOADS_PATH."backup-$name-$version.zip";
			$zip = new \ZipArchive();
			$zip->open($backup, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
			
			$files = new \RecursiveIteratorIterator( 
				new \RecursiveDirectoryIterator(ABSPATH),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);
	
			$uploads = ABSPATH.File::UPLOADS_PATH;
			foreach ($files as $name => $file){
				$filePath = $file->getRealPath();
				// TODO: exclude directories
				if ( !$file->isDir() && strpos($filePath, $uploads) === false ){
					$relativePath = mb_substr($filePath, mb_strlen(ABSPATH));
					$zip->addFile($filePath, $relativePath);
				}
			}
			$zip->close();
		}
	}

	private function extractUpdate($installPath, $package){
		if( !file_exists($package) ){
			return uCMS::ERR_NO_UPDATE_PACKAGE;
		}
		if( is_writable($installPath) ){
			$zip = new \ZipArchive();
			$res = $zip->open($package);
			if($res === TRUE){
				$zip->extractTo($installPath);
				$zip->close();
				return uCMS::SUCCESS;
			}else{
				return uCMS::ERR_INVALID_PACKAGE;
			}
		}
		return uCMS::ERR_NO_PERMISSIONS;
	}

	private function addSettings($name, $description, $title, $domain, $ucmsDir){
		$query = new Query("{settings}");
		$language = Session::GetCurrent()->get('language');
		$query->insert(['name', 'value', 'owner', 'changed'], 
			[
				[Setting::ADMIN_EMAIL,         '',           'core', 0],
				[Setting::BLOCKS_AMOUNT,       '',           '',     0],
				[Setting::CLEAN_URL,           '0',          'core', 0],
				[Setting::DATETIME_FORMAT,     'Y-m-d H:i',  'core', 0],
				[Setting::DO_UPDATE_BACKUP,    '1',          'core', 0],
				[Setting::EMBEDDING_ALLOWED,   '0',          'core', 0],
				[Setting::ENABLE_CACHE,        '1',          'core', 0],
				[Setting::EXTENSIONS,          '',           'core', 0],
				[Setting::INSTALLED_TABLES,    '',           'core', 0],
				[Setting::LANGUAGE,            $language,    'core', 0],
				[Setting::MAINTENANCE_MESSAGE, '',           'core', 0],
				[Setting::PER_PAGE,            '20',         'core', 0],
				[Setting::SITE_AUTHOR,         '',           'core', 0],
				[Setting::SITE_DESCRIPTION,    $description, 'core', 0],
				[Setting::SITE_DOMAIN,         $domain,      'core', 0],
				[Setting::SITE_NAME,           $name,        'core', 0],
				[Setting::SITE_TITLE,          $title,       'core', 0],
				[Setting::THEME,               '',           'core', 0],
				[Setting::UCMS_DIR,            $ucmsDir,     'core', 0],
				[Setting::UCMS_MAINTENANCE,    '1',          'core', 0],
				[Setting::UCMS_TIMEZONE,       'UTC',        'core', 0]
			], true
		);
		$query->execute();
	}

	private function getDefaultSchemas(){
		$schemas['settings'] = [
			'fields' => [
				'name' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'value' => [
					'type' => 'text',
					'size' => 'big',
					'not null' => true
				],
				'owner' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'changed' => [
					'type' => 'int',
					'default' => time(),
					'not null' => true
				]
			],
			'primary key' => 'name'
		];

		$schemas['blocks'] = [
			'fields' => [
				'bid' => [
					'type' => 'serial',
					'not null' => true
				],
				'name' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'title' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'owner' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'status' => [
					'type' => 'int',
					'default' => 0
				],
				'theme' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'region' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'visibility' => [
					'type' => 'int',
					'size' => 'tiny',
					'not null' => true
				],
				'actions' => [
					'type' => 'text',
					'not null' => true
				],
				'cache' => [
					'type' => 'int',
					'default' => 0
				],
				'position' => [
					'type' => 'int',
					'default' => 0
				]
			],
			'primary key' => 'bid',
			'unique keys' => [ 'block' => ['name', 'owner', 'theme'] ]
		];

		$schemas['cache'] = [
			'fields' => [
				'cid' => [ 
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'data' => [ 
					'type' => 'blob',
					'size' => 'big',
					'not null' => true
				],
				'raw' => [ 
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'expires' => [ 
					'type' => 'int',
					'default' => 0
				],
				'created' => [ 
					'type' => 'int',
					'default' => time(),
					'not null' => true
				]
			],
			'primary key' => 'cid'
		];

		$schemas['ips'] = [
			'fields' => [
				'ip' => [
					'type' => 'varchar',
					'length' => '40',
					'not null' => true,
				],
				'attempts' => [
					'type' => 'int',
					'default' => 0,
				],
				'blocked' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				]
			],
			'primary key' => 'ip'
		];

		$schemas['sessions'] = [
			'fields' => [
				'uid' => [
					'type' => 'int',
					'not null' => true
				],
				'sid' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'ip' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'sessiondata' => [
					'type' => 'blob',
					'size' => 'big',
					'not null' => true
				],
				'created' => [
					'type' => 'int',
					'not null' => true
				],
				'updated' => [
					'type' => 'int',
					'not null' => true
				],
			],
			'primary key' => 'sid'
		];
		return $schemas;
	}

}
?>