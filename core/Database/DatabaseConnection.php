<?php
namespace uCMS\Core\Database;
use uCMS\Core\Debug;
use uCMS\Core\Loader;
use uCMS\Core\Page;
use uCMS\Core\Tools;
class DatabaseConnection{
	const DISPLAY_QUERY = false;
	const DEFAULT_NAME = 'default';
	const ERR_TABLE_NOT_EXIST = "42S02";
	const ERR_DRIVER_NOT_LOADED = 10000;
	const SCHEMA_CORRECT = 0;
	const ERR_SCHEMA_WRONG_KEY = 200;
	const ERR_SCHEMA_NO_FIELDS = 300;
	const ERR_SCHEMA_NO_FIELD_NAME = 400;
	const ERR_SCHEMA_WRONG_FIELD = 500;
	const ERR_SCHEMA_WRONG_FIELD_VALUE = 600;
	const ERR_SCHEMA_WRONG_TYPE = 700;
	const ERR_SCHEMA_WRONG_SIZE = 800;
	const ERR_SCHEMA_NO_TYPE = 900;
	const ERR_SCHEMA_NO_PRECISION = 1000;
	const ERR_SCHEMA_NO_SCALE = 1100;
	const ERR_SCHEMA_WRONG_ENGINE = 1200;
	const ERR_SCHEMA_WRONG_VALUE = 1300;

	private static $default;
	private static $databases = [];
	private static $supportedDrivers = ['mysql'];
	private $dbServer, $dbUser, $dbPassword, $dbName;
	private $driver;
	private $tables;
	private $queriesCount;
	private $connection;
	private $connected = false;
	private $prefix;
	private $ucmsName;
	private $lastStatement = NULL;

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
				if( $e->getCode() === self::ERR_DRIVER_NOT_LOADED ){
					Loader::GetInstance()->panic($e->getMessage());
				}

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

	public function checkDriver(){
		if (!extension_loaded('pdo_'.$this->driver)) {
			throw new \RuntimeException(tr("Database Connection Error, @s driver is not loaded", $this->driver), self::ERR_DRIVER_NOT_LOADED);
		}
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

	public function __construct($server, $login, $password, $dbName, $dbPort, $prefix, $ucmsName, $driver = ""){
		$this->dbServer = $server;
		$this->dbUser = $login;
		$this->dbPassword = $password;
		$this->dbName = $dbName;
		$this->dbPort = (int) $dbPort;
		$this->prefix = $prefix;
		$this->ucmsName = $ucmsName;
		$this->driver = (!empty($driver) && in_array($driver, self::$supportedDrivers)) ? $driver : 'mysql';
		$this->checkDriver();
		$this->connect();
		$this->connected = true;
		if($ucmsName == self::DEFAULT_NAME){
			$this->setDefaultTables();
			self::$default = $this;
		}
	}

	public function connect(){
		$this->connection = new \PDO($this->driver.":host=$this->dbServer;port=$this->dbPort;dbname=$this->dbName;charset=utf8", $this->dbUser, $this->dbPassword);
		
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

		if( is_object($this->lastStatement) ){
			$this->lastStatement->closeCursor();
		}

		try{
			$result->execute($params);
			$this->lastStatement = $result;
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
				Debug::Log(tr('Query failed: @s, error: @s', $sql, $e->getMessage()), Debug::LOG_ERROR);
				return false;
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
				$fullTable = $this->prefix.$table;
				$exists = $this->isTableExists($fullTable);
				$data[$table] = $exists;
			}
			return $data;
		}
	}

	public function isTableExists($table){
		$table = Tools::PrepareSQL($table);
		$exists = true;
		try{
			$this->connection->query("SELECT 1 FROM $table LIMIT 1");
			$exists = true;
		}catch(\PDOException $e){
			if( $e->getCode() === self::ERR_TABLE_NOT_EXIST ){
				$exists = false;
			}
		}
		return $exists;
	}

	public function setDefaultTables(){
		$this->tables = array('settings', 'blocks', 'cache', 'ips', 'sessions');
	}

	public function getuCMSName(){
		return $this->ucmsName;
	}

	public static function CheckSchema(array $schema){
		// Schema definition is almost compatible with Drupal
		$baseKeys = [
			'description', // Description of table
			'fields', // And array of fields
			'unique keys', // An associative array of unique keys ('keyname' => specification). Each specification is an array of one or more key column specifiers.
			'indexes', //An associative array of indexes ('indexname' => specification). Each specification is an array of one or more key column specifiers that form an index on the table.
			'primary key', // Primary key for table
			'mysql_engine', // In MySQL databases, the engine to use instead of the default
			'mysql_character_set', // In MySQL databases, the character set to use instead of the default.
			'collation' // In MySQL databases, the collation to use instead of the default.
		];

		$keys = [
			'description', // Description of field
			'type', // Type of field
			'mysql_type', // Database driver dependent type
			'size', // Data size
			'not null', // If true, no null values will be allowed in field. Default: false
			'default', // Default value for field
			'length', // Length of length
			'unsigned', // A boolean indicating whether a type 'int', 'float' and 'numeric' only is signed or unsigned.
			'precision', // For type 'numeric' fields, indicates the precision (total number of significant digits) and scale (decimal digits right of the decimal point). 
			'scale', // See previous
			'serialize', // A boolean indicating whether the field will be stored as a serialized string.
			'binary', // A boolean indicating that MySQL should force 'char', 'varchar' or 'text' fields to use case-sensitive binary collation. This has no effect on other database types for which case sensitivity is already the default behavior.
			
		];
		$arrayKeys = ['fields', 'unique keys', 'indexes'];
		$engines = ['InnoDB', 'MyISAM'];
		$types = ['varchar', 'char', 'int', 'serial', 'float', 'numeric', 'text', 'blob', 'datetime'];
		$sizes = ['tiny', 'small', 'medium', 'normal', 'big'];
		$hasFields = false;
		$hasType = false;

		foreach ($schema as $key => $value) {
			if( !in_array($key, $baseKeys) ) return self::ERR_SCHEMA_WRONG_KEY;
			if( in_array($key, $arrayKeys) && !is_array($value) ) return self::ERR_SCHEMA_WRONG_VALUE;
			if( $key === 'fields' ){
				if( count($value) == 0 ) return self::ERR_SCHEMA_NO_FIELDS;
				$hasFields = true;
				foreach ($value as $columnName => $columnData) {
					$precisionSet = -1;
					$scaleSet = -1;
					if( $columnName == "" ) return self::ERR_SCHEMA_NO_FIELD_NAME;
					$hasType = false;
					foreach ($columnData as $fieldKey => $fieldValue) {
						if( is_int($fieldKey) ) continue;
						if( !in_array($fieldKey, $keys) ) return self::ERR_SCHEMA_WRONG_FIELD;
						
						if( $fieldKey === 'type' ){
							
							if( !in_array($fieldValue, $types) ) return self::ERR_SCHEMA_WRONG_TYPE;
							if( $fieldValue == 'numeric' ){
								// We set flags to search precision and scale columns
								$precisionSet = 0;
								$scaleSet = 0;
							}
							$hasType = true;
						}

						if( $fieldKey === 'size' ){
							if( !in_array($fieldValue, $sizes) ) return self::ERR_SCHEMA_WRONG_SIZE;
						}
	
						if( $fieldKey === 'precision' ){
							$precisionSet = 1;
						}
	
						if( $fieldKey === 'scale' ){
							$scaleSet = 1;
						}
					}
					if( !$hasType ) return self::ERR_SCHEMA_NO_TYPE;
					if( $precisionSet == 0 ) return self::ERR_SCHEMA_NO_PRECISION;
					if( $scaleSet == 0 ) return self::ERR_SCHEMA_NO_SCALE;
				}
			}

			if( $key == 'mysql_engine' ){
				if( !in_array($value, $engines) ) return self::ERR_SCHEMA_WRONG_ENGINE;
			}
		}
		return self::SCHEMA_CORRECT;
	}

	public function getSQLSize($type, $size, $precision = 0, $scale = 0){
		// TODO: Call driver-specific method
		$types = ['varchar', 'char', 'int', 'serial', 'float', 'numeric', 'text', 'blob', 'datetime'];
		$sizes = ['tiny', 'small', 'medium', 'normal', 'big'];
		if( !in_array($type, $types) ) return 0;
		if( !in_array($size, $sizes) ) return 0;
		if( $type == 'serial' || $type == 'int '){
			$prefix = $size != 'normal' ? $size : '';
			return $prefix.'int';
		}

		if( $type == 'float' ){
			if( $size == 'big' ) return 'double';
			return 'float';
		}

		if( $type == 'numeric' ){
			return "decimal($precision, $scale)";
		}

		if( $type == 'varchar' ){
			$length = 64;
			if( $size == 'big')
				$length = 255;
			return $type."($length)";
		}

		if( $type == 'text' || $type == 'blob' ){
			$prefix = ($size != 'big' && $size != 'small' && $size != 'normal') ? $size : '';
			if( $size == 'big' ) $prefix = 'long';
			return $prefix.$type;
		}

		return $type;
	}

}
?>