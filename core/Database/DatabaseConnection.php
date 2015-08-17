<?php
namespace uCMS\Core\Database;
use uCMS\Core\Debug;
use uCMS\Core\Loader;
use uCMS\Core\Page;
class DatabaseConnection{
	const DISPLAY_QUERY = false;
	const DEFAULT_NAME = 'default';
	const ERR_TABLE_NOT_EXIST = "42S02";
	private static $default;
	private static $databases = array();
	private $dbServer, $dbUser, $dbPassword, $dbName;
	private $tables;
	private $queriesCount;
	private $connection;
	private $connected = false;
	private $prefix;
	private $ucmsName;

	public static function Init(){
		if( empty($GLOBALS['databases']) || !is_array($GLOBALS['databases']) ){
			Loader::GetInstance()->install();
			Debug::Log(tr("No configuration file was found"), Debug::LOG_CRITICAL);
		}
		foreach ($GLOBALS['databases'] as $dbName => $dbData) {
			try{
				$fields = array('server', 'user', 'password', 'name', 'port', 'prefix');
				foreach ($fields as $field) {
					if( !isset($dbData[$field]) ){
						Debug::Log(tr("Wrong configuration file was provided"), Debug::LOG_CRITICAL);
						Loader::GetInstance()->install();
					}
				}
				$database = new DatabaseConnection(
					$dbData["server"], 
					$dbData["user"], 
					$dbData["password"], 
					$dbData["name"], 
					$dbData["port"], 
					$dbData["prefix"],
					$dbName
				);
				
				/**
				* @todo check mysql version
				*/
				self::$databases[$dbName] = $database;
			}catch(\Exception $e){
				if( $e->getCode() == 1045 || $e->getCode() == 1049 ){
					Debug::Log(tr("Wrong configuration file was provided"), Debug::LOG_CRITICAL);
					Loader::GetInstance()->install();
				}else{
					Debug::Log(tr("Database @s connection error @s: @s", $dbName, $e->getCode(), $e->getMessage()), Debug::LOG_CRITICAL);
				}
			}
		}
		unset($GLOBALS['databases']); // We don't want to have global variables, so we delete this
	}

	public function isConnected(){
		return $this->connected;
	}

	public static function GetDefault(){
		if( !is_null(self::$default) ){
			return self::$default;
		}
	}

	public static function GetDatabase($name){
		if( isset(self::$databases[$name]) ){
			return self::$databases[$name];
		}
		return self::$default;
	}

	public static function Shutdown(){
		foreach (self::$databases as $connection) {
			$connection->close();
		}
	}

	public function __construct($server, $login, $password, $dbName, $dbPort, $prefix, $ucmsName){
		$this->dbServer = $server;
		$this->dbUser = $login;
		$this->dbPassword = $password;
		$this->dbName = $dbName;
		$this->dbPort = (int) $dbPort;
		$this->prefix = $prefix;
		$this->ucmsName = $ucmsName;
		$this->connect();
		$this->connected = true;
		if($ucmsName == self::DEFAULT_NAME){
			$this->setDefaultTables();
			self::$default = $this;
		}
	}

	public function connect(){
		$this->connection = new \PDO("mysql:host=$this->dbServer;port=$this->dbPort;dbname=$this->dbName;charset=utf8", $this->dbUser, $this->dbPassword);
		
		$this->connection->exec("set names utf8");
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		//if ($this->connection->connect_errno) {
		//	throw new Exception("Can't connect to database", $this->connection->connect_errno);
		//}
		//if (!$this->connection->set_charset("utf8")) {
		//	throw new Exception("Can't set database charset");
		//}

	}

	public function close(){
		$this->connection = null; 
	}

	public function doQuery($sql, $params = array()){
		if($sql == "") return false;
		$result = $this->connection->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		if(self::DISPLAY_QUERY) {
			Debug::BeginBlock();
			echo $sql;
			Debug::EndBlock();
		}
		try{
			$result->execute($params);
			$this->queriesCount++;
		}catch(\PDOException $e){
			if( $e->getCode() === self::ERR_TABLE_NOT_EXIST ){
				// TODO: debug mode
				// TODO: check only if this is installed table
				$check = Page::Install('check');
				if( Page::GetCurrent()->getAction() !== Page::INSTALL_ACTION ){
					$check->go();
				}
			}else{
				Debug::BeginBlock();
				echo "<h2>".tr("Query failed")."</h2><br>";
				echo "$sql<br><br>";
				echo $e->getMessage();
				if(UCMS_DEBUG){
					echo "<br><h3>Trace:</h3>".$e->getTraceAsString();
				}
				Debug::EndBlock();
			}
			
		}
		// $result->close();
		return $result;
	}

	public function escapeString($value){
		return $this->connection->quote($value);
	}

	public function fetch($query, $type = 'assoc'){
		switch ($type) {
			case 'assoc':
				$outType = \PDO::FETCH_ASSOC;
			break;
			
			case 'object':
			$outType = \PDO::FETCH_OBJ;
			break;

			default:
				$outType = \PDO::FETCH_ASSOC;
			break;
		}

		if( is_object($query) && $query instanceof \PDOStatement ){
			return $query->fetch($outType); //param
		}
		return false;
	}

	public function getQueriesCount(){
		return $this->queriesCount;
	}

	public function getConnection(){
		return $this->connection;
	}

	public function getPrefix(){
		return $this->prefix;
	}

	public function getTable($name){
		foreach ($this->tables as $table) {
			if($table === $name){
				return $this->getPrefix().$table;
			}
		}
	}

	public function checkDefaultTables(){
		if( $this->ucmsName === self::DEFAULT_NAME ){
			$data = array();
			$exists = false;
			foreach ($this->tables as $table) {
				try{
					$fullTable = $this->prefix.$table;
					$this->connection->query("SELECT 1 FROM $fullTable LIMIT 1");
					$exists = true;
				}catch(\PDOException $e){
					if( $e->getCode() === self::ERR_TABLE_NOT_EXIST ){
						$exists = false;
					}
				}
				$data[$table] = $exists;
			}
			return $data;
		}
	}

	public function setDefaultTables(){
		$this->tables = array('settings', 'blocks', 'cache', 'ips', 'sessions', 'menus');
	}

	public function getuCMSName(){
		return $this->ucmsName;
	}

}
?>