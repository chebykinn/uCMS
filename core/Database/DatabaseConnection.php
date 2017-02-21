<?php
namespace uCMS\Core\Database;
use uCMS\Core\Debug;
use uCMS\Core\Loader;
use uCMS\Core\Page;
use uCMS\Core\Object;
class DatabaseConnection extends Object{
	const DISPLAY_QUERY = false;
	const DEFAULT_NAME = 'default';
	private static $default;
	private static $databases = [];
	private static $supportedDrivers = ['mysql', 'pgsql'];
	private $dbServer, $dbUser, $dbPassword, $dbName;
	private $driver;
	private $tables;
	private $queriesCount;
	private $connection;
	private $connected = false;
	private $prefix;
	private $ucmsName;
	private $lastStatement = NULL;
	private $driverClass = "";

	public static function Init(){
		if( empty($GLOBALS['databases']) || !is_array($GLOBALS['databases']) ){
			Debug::Log(self::Translate("No configuration file was found"), Debug::LOG_CRITICAL);
			Loader::GetInstance()->install();
		}

		foreach ($GLOBALS['databases'] as $dbName => $dbData) {
			try{
				$fields = array('server', 'user', 'password', 'name', 'port', 'prefix');
				foreach ($fields as $field) {
					if( !isset($dbData[$field]) ){
						Debug::Log(self::Translate("Wrong configuration file was provided"), Debug::LOG_CRITICAL);
						Loader::GetInstance()->install();
					}
				}
				if( !empty($dbData["driver"]) ){
					$driver = $dbData["driver"];
				}else{
					$driver = self::$supportedDrivers[0];
				}
				$database = new DatabaseConnection(
					$dbData["server"], 
					$dbData["user"], 
					$dbData["password"], 
					$dbData["name"], 
					$dbData["port"], 
					$dbData["prefix"],
					$dbName,
					$driver,
					$dbData
				);

				self::$databases[$dbName] = $database;
			}
			catch(\PDOException $e){
				Debug::Log(self::Translate("Unable to connect to database \"@s\": <br><pre>@s</pre>", $dbName, $e->getMessage()), Debug::LOG_CRITICAL);
				Loader::GetInstance()->install();
			}
			catch(\RuntimeException $e){
				Loader::GetInstance()->panic($e->getMessage());
			}
			catch(\UnderflowException $e){
				Loader::GetInstance()->panic($e->getMessage());
			}
			catch(\Exception $e){
				Loader::GetInstance()->panic($e->getMessage());
			}
		}
		unset($GLOBALS['databases']); // We don't want to have global variables, so we delete this
	}

	public function getDriver(){
		return $this->driver;
	}

	public function isConnected(){
		return $this->connected;
	}

	public static function GetDefault(){
		if( !is_null(self::$default) ){
			return self::$default;
		}
	}

	public static function HasDatabase($name){
		return ( isset(self::$databases[$name]) );
	}

	public static function GetDatabase($name){
		if( self::hasDatabase($name) ){
			return self::$databases[$name];
		}
		return self::$default;
	}

	public static function Shutdown(){
		foreach (self::$databases as $connection) {
			$connection->close();
		}
	}

	public function __construct($server, $login, $password, $dbName, $dbPort, $prefix, $ucmsName, $driver = "", $dbData = []){
		parent::__construct();
		$this->dbServer = $server;
		$this->dbUser = $login;
		$this->dbPassword = $password;
		$this->dbName = $dbName;
		$this->dbPort = (int) $dbPort;
		$this->prefix = $prefix;
		$this->ucmsName = $ucmsName;
		$driver = (!empty($driver) && in_array($driver, self::$supportedDrivers)) ? $driver : 'mysql';
		$this->driverClass = __NAMESPACE__."\\Driver\\$driver\\Driver";
		$this->driver = new $this->driverClass();
		$this->driver->check();
		$this->driver->setup($dbData);
		$this->connect();
		$this->connected = true;
		if($ucmsName == self::DEFAULT_NAME){
			$this->setDefaultTables();
			self::$default = $this;
		}
	}

	public function connect(){
		$dsn = $this->driver->name.":host=$this->dbServer;port=$this->dbPort;dbname=$this->dbName";
		$opt = [
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
		];
		$this->connection = new \PDO($dsn, $this->dbUser, $this->dbPassword, $opt);
		$version = $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);

		if( version_compare($version, $this->driver::MIN_VERSION, '<') ){
			throw new \UnderflowException($this->tr("@s: Outdated version @s. Minimum required: @s",
											$this->driver->name, $version, $this->driver::MIN_VERSION));
		}
		$this->driver->prepareConnection($this->connection);

	}

	public function close(){
		$this->connection = NULL; 
	}

	public function doQuery($sql, $params = []){
		if($sql == "") return false;
			// 	\uCMS\Core\Debug::PrintVar($this->ucmsName);
			// 	\uCMS\Core\Debug::PrintVar('conn:<br>');
			// 	\uCMS\Core\Debug::PrintVar($this->connection);
			// 	Debug::BeginBlock(false);
			// debug_print_backtrace();
			// Debug::EndBlock();
		$result = $this->connection->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
		if(self::DISPLAY_QUERY) {
			Debug::BeginBlock(false);
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
			if( $e->getCode() === ($this->driverClass)::ERR_TABLE_NOT_EXIST && !UCMS_DEBUG ){
				$check = Page::Install('check');
				$check->go();
			}else{
				Debug::BeginBlock();
				echo "<h2>".$this->tr("Query failed")."</h2><br>";
				echo "$sql<br><br>";
				echo $e->getMessage();
				if(UCMS_DEBUG){
					echo "<br><h3>Trace:</h3>".$e->getTraceAsString();
				}
				Debug::EndBlock();
				Debug::Log($this->tr('Query failed: @s, error: @s', $sql, $e->getMessage()), Debug::LOG_ERROR, $this);
				return false;
			}
			
		}
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
			return $query->fetch($outType);
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
			$data = [];
			$exists = false;
			foreach ($this->tables as $table) {
				$query = new Query("{$table}", [], $this->ucmsName);
				$exists = $query->tableExists()->execute();
				$data[$table] = $exists;
			}
			return $data;
		}
	}

	public function setDefaultTables(){
		$this->tables = ['settings', 'blocks', 'cache', 'ips', 'sessions'];
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
			if( !in_array($key, $baseKeys) ){
				throw new \ArgumentException("Wrong key in schema: @s", $key);
			}
			if( in_array($key, $arrayKeys) && !is_array($value) ){
				throw new \ArgumentException("Value for key @s should be an array", $key);
			}
			if( $key === 'fields' ){
				if( count($value) == 0 ){
					throw new \ArgumentException("No fields in schema");
				}
				$hasFields = true;
				foreach ($value as $columnName => $columnData) {
					$precisionSet = -1;
					$scaleSet = -1;
					if( $columnName == "" ){
						throw new \ArgumentException("Empty field name");
					}
					$hasType = false;
					foreach ($columnData as $fieldKey => $fieldValue) {
						if( is_int($fieldKey) ) continue;
						if( !in_array($fieldKey, $keys) ){
							throw new \ArgumentException("Wrong key @s in column @s", $fieldKey, $columnName);
						}
						
						if( $fieldKey === 'type' ){
							
							if( !in_array($fieldValue, $types) ){
								throw new \ArgumentException("Wrong type @s in column @s", $fieldValue, $columnName);
							}
							if( $fieldValue == 'numeric' ){
								// We set flags to search precision and scale columns
								$precisionSet = 0;
								$scaleSet = 0;
							}
							$hasType = true;
						}

						if( $fieldKey === 'size' ){
							if( !in_array($fieldValue, $sizes) ){
								throw new \ArgumentException("Wrong size @s in column @s", $fieldValue, $columnName);
							}
						}
	
						if( $fieldKey === 'precision' ){
							$precisionSet = 1;
						}
	
						if( $fieldKey === 'scale' ){
							$scaleSet = 1;
						}
					}
					if( !$hasType ) {
						throw new \ArgumentException("No type specified for column @s", $columnName);
					}
					
					if( $precisionSet == 0 ){
						throw new \ArgumentException("No precision specified for numeric type in column @s", $columnName);
					}

					if( $scaleSet == 0 ){
						throw new \ArgumentException("No scale specified for numeric type in column @s", $columnName);
					}
				}
			}

			if( $key == 'mysql_engine' ){
				if( !in_array($value, $engines) ){
					throw new \ArgumentException("Wrong MySQL engine", $value);
				}
			}
		}
	}
}
?>
