<?php
namespace uCMS\Core;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\Language\Language;

class Installer{
	private $stageCallback = "welcome";
	private $currentStage = "";
	const LANGUAGE_STAGE = 'language';
	const WELCOME_STAGE = 'welcome';
	const CONFIG_STAGE = 'config';
	const CONNECT_ERROR_STAGE = 'connectionError';
	const CHECK_STAGE = 'check';
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

	public static function IsRunning(){
		return !is_null( self::$instance );
	}

	public function __construct() {
		// Detect current stage
		$this->currentStage = Session::GetCurrent()->get('install-stage');
		if( empty($this->currentStage) ){
			$this->checkStage();
		}

		self::$instance = $this;
	}

	private function checkStage(){
		// First we check configuration file
		$connected = is_object(DatabaseConnection::GetDefault()) ? DatabaseConnection::GetDefault()->isConnected() : false;
		if (!$connected) {
			if( !$this->isLanguageSet() ){
				$this->setStage(self::LANGUAGE_STAGE);
			}else{
				if( $this->isConfigFileExists() ){
					$this->setStage(self::CONNECT_ERROR_STAGE);
				// Session::GetCurrent()->set('install-stage', self::CONNECT_ERROR_STAGE);
				// $error = Page::Install($stage);
				// $error->go();
				}else{
					$this->setStage(self::WELCOME_STAGE);
				}
			}
		}else{
			$this->setStage(self::CHECK_STAGE);
		}
	}


	private function setStage($stage){
		if( $this->currentStage !== $stage && !empty($stage) ){
			Session::GetCurrent()->set('install-stage', $stage);
			$this->currentStage = $stage;
			// $page = Page::Install($stage);
			// $page->go();
		}
	}

	private function switchStage($stage){
		if( $this->currentStage !== $stage && !empty($stage) ){
			$this->setStage($stage);
			$page = Page::Install($stage);
			$page->go();
		}
	}

	public function isLanguageSet(){
		$language = Session::GetCurrent()->get('language');
		return !empty($language);
	}

	public function isConfigFileExists(){
		$configFirst = ABSPATH.'config.php';
		$configUpper = dirname(ABSPATH).'/config.php';
	
		return ( (file_exists($configFirst) && is_file($configFirst))
			  || (file_exists($configUpper) && is_file($configUpper)) );
	}

	public function setTitle($newTitle = ""){
		$delimeter = " :: ";
		if( empty($newTitle) ) $delimeter = "";
		$title = tr('µCMS Installation').$delimeter.$newTitle;
		Theme::GetCurrent()->setTitle($title);
	}

	public function run(){
		if( Page::GetCurrent()->getAction() != Page::INSTALL_ACTION 
		 || Page::GetCurrent()->getActionData() != $this->currentStage ){
			$installPage = Page::Install($this->currentStage);
			$installPage->go();
		}else{
			try{
				Theme::SetCurrent('install');
			}catch(\InvalidArgumentException $e){
				p("[@s]: ".$e->getMessage(), 'Installer');
			}catch(\RuntimeException $e){
				p("[@s]: ".$e->getMessage(), 'Installer');
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
				$this->setTitle('Select Language');
				if( $isPosted ){
					$language = isset($_POST['language']) ? $_POST['language'] : 'en_US';
					Session::GetCurrent()->set('language', $language);
				}
			break;

			case self::WELCOME_STAGE:
				$this->setTitle('Welcome!');
				if( $isPosted ){
					$nextStage = self::CONFIG_STAGE;
				}
			break;

			case self::CONFIG_STAGE:
				$this->setTitle('Database Connection Configuration');
				$server   = !empty($_POST['server']) ? $_POST['server'] : 'localhost';
				if( $isPosted ){
					$port     = explode(":", $server);
					$dbPort   = !empty($port[1]) ? (int) $port[1] : "";
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
						$fields = array("%name%", "%server%", "%port%", "%user%", "%password%", "%prefix%");
						$values = array($dbName, $server, $dbPort, $login, $password, $prefix);
						$config = file_get_contents(ABSPATH.uCMS::CONFIG_SAMPLE);
						$config = str_replace($fields, $values, $config);
						$file = @fopen(ABSPATH.uCMS::CONFIG_FILE, "w+");
						if( $file === false){
							$nextStage = self::CONNECT_ERROR_STAGE;
						}else{
							fprintf($file, '%s', $config);
							fclose($file);
							$nextStage = self::CHECK_STAGE;
						}
					}catch(\PDOException $e){
						$nextStage = self::CONNECT_ERROR_STAGE;
					}
					// Data is correct now we have to create config.php
				}
			break;

			case self::CONNECT_ERROR_STAGE:
				$this->setTitle('Error Connecting To Database');
				if( $isPosted ){
					$nextStage = self::CONFIG_STAGE;
				}
			break;

			case self::TABLES_STAGE:
				$this->setTitle('Creating Tables');
				if( $isPosted ){
					// TODO: Implement
					echo 'making tables';
					exit;
				}
			break;

			case self::CHECK_STAGE:
				$this->setTitle('Checking State...');
				// Check core tables
				$tables = DatabaseConnection::GetDefault()->checkDefaultTables();
				if( in_array(false, $tables) ){
					$nextStage = self::TABLES_STAGE;
				}else{
					// Check settings table
					$nextStage = $this->checkSettings();
					// Load extensions checks
					if( empty($nextStage) ){
						$needInstall = Extension::CheckInstall();
						if( $needInstall ){
							$nextStage = self::EXTENSIONS_STAGE;
						}
					}
				}
				$this->switchStage($nextStage);
			break;

			case self::SITEINFO_STAGE:
				$this->setTitle('Site Information');
				if( $isPosted ){
					// TODO: Implement
					echo 'adding info';
					exit;
				}
			break;

			case self::EXTENSIONS_STAGE:
				if( $isPosted ){
					Extension::Install(self::CHECK_STAGE);
				}
			break;

			case self::UPDATE_STAGE:
			break;

			case self::FINE_STAGE:
				$this->setTitle('Everything\'s Fine');
			break;

			case self::DONE_STAGE:
			
			break;
		}
		if( $isPosted ){
			$this->switchStage($nextStage);
		} 
	}

	public function printStage(){
		print "<h2>".Theme::GetCurrent()->getTitle()."</h2>";
		$this->stageCallback();
	}

	private function installForm($name, $button = ''){
		if ( empty($button) ){
			$button = tr('Next');
		}
		$form = $form = new Form($name, Page::Install($this->currentStage), $button);
		$form->addHiddenField("stage", $this->currentStage);
		return $form;
	}

	private function languageStage(){
		$form = $this->installForm('language-form', 'Continue');
		$languages = array(
			"en_US" => "English",
			"ru_RU" => "Russian"
		); // TODO: Generate list of languages
		$form->addSelectField($languages, "languages", "", "", "en_US", 16);
		$form->render();
	}

	private function welcomeStage(){
		print '<p>';
		p('Welcome to μCMS! Before you can enjoy your site, we need some information on database, where all data will be stored.<br>After a few steps of configuration you\'ll be ready to use your site.');
		print '</p>';

		$form = $this->installForm('welcome-form', tr('Let\'s do this!'));
		$form->render();
	}

	private function configStage(){
		$form = $this->installForm('config-form');
		$form->addField('server', 'text', tr('Database Server:'), tr('Most likely it\'s "localhost".'), 'localhost', tr('server'));
		$form->addField('user', 'text', tr('User:'), tr('Login of database user, provided by website hosting.'), 'root', tr('user'));
		$form->addField('password', 'text', tr('Password:'), tr('Password of database user, provided by website hosting.'), '', tr('password'), false);
		$form->addField('name', 'text', tr('Database name:'), tr('Name of database, where you want to install μCMS.'), 'ucms', tr('name'));
		$form->addField('prefix', 'text', tr('Tables prefix:'), tr('You can change it to install different μCMS sites in one database.'), 'uc_', tr('prefix'));
		$form->render();
		print '<p>';
		p("<b>Note:</b> If config.php will not be created you can use config-manual.php to create it yourself.");
		print '</p>';
	}

	private function connectionErrorStage(){
		print '<p>';
		p("An error occurred while connecting to database, this means either you have provided incorrect username and password, or we can't reach your database server.<br>Please make sure that you're entered correct data and try again. If this error still occurs, contact server administrator.");
		print '</p>';

		$form = $this->installForm('error-form', tr('Try again'));
		$form->render();
	}

	private function tablesStage(){
		print '<p>';
		p('There is lack of our tables in your database, need to create some!');
		print '</p>';
		$form = $this->installForm('allow-tables', tr('Alrighty then!'));
		$form->render();
	}

	private function siteInformationStage(){
		print '<p>';
		p('Just a few more steps to go!');

		$form = $this->installForm('info-form');
		$form->addField('name', 'text', tr('Site Name:'), tr('Give yor site a name, like: "Bob\'s site".'), 'Site on μCMS', tr('Name'));
		$form->addField('description', 'text', tr('Site Description:'), tr('Describe your site, like: "Cool site about me!".'), 'The site indeed', tr('Description'));
		$form->addField('title', 'text', tr('Site Title:'), tr('Displayed at the top of the browser\'s page,<br> for instance: "Bob\'s site is the best site in the world!".'), 'The Best Site on μCMS', tr('Title'));
		$form->render();
		print '</p>';
	}

	private function extensionsStage(){
		Extension::Install('print');
	}

	private function checkSettings(){
		// TODO: Implement
		return self::SITEINFO_STAGE;
	}

	private function updateStage(){
		// TODO: Implement

	}

	private function doneStage(){
		// TODO: Implement

	}

	public function fineStage(){
		print '<p>';
		p('μCMS is already installed and everything is working flawlessly (according to our checks).<br> To reinstall μCMS please delete our tables or configuration file.');
		print '</p>';
		$homePage = Page::Home();
		print '<br><a class="button" href="'.$homePage.'">'.tr('Back to the home page').'</a>';
	}

	public function __call($method, $args){
		if( method_exists($this, $this->stageCallback) ){
			return call_user_func_array(array($this, $this->stageCallback), $args);
		}
    }

    private function addSettings(){

    }
}
?>