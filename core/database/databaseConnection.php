<?php
class DatabaseConnection{
	private static $instance;
	private $dbServer, $dbUser, $dbPassword, $dbName;
	private $tables;
	private $queriesCount;
	private $connection;
	private $prefix;
	private $ucmsName;

	public function getDefault(){
		if( !is_null(self::$instance) ){
			return self::$instance;
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
		if($ucmsName == DEFAULT_DATABASE_NAME){
			$this->setDefaultTables();
			self::$instance = $this;
		}
	}

	public function connect(){
		$this->connection = mysqli_connect($this->dbServer, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPort);
		if(!$this->connection){
			$errno = mysqli_connect_errno();
			throw new Exception("Can't connect to database", $errno);
		}

	}

	public function disconnect(){
		
	}

	public function doQuery($sql){
		if($sql == "") return false;
		$query = @mysqli_query($this->connection, $sql);
		$this->queriesCount++;
		if(DEBUG_DISPLAY_QUERY) {
			echo "<pre style=\"text-align: left; color: #000; background: #fff; border: 1px #555 solid; margin: 20px; padding: 5px; z-index: 9999;\">$sql</pre>";
		}
		return $query;
	}

	public function escapeString($value){
		return @mysqli_escape_string($this->connection, $value);
	}

	public function fetchArray($query){
		return @mysqli_fetch_array($query);
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

	public function setDefaultTables(){
		$this->tables = array('settings');
	}

	public function getuCMSName(){
		return $this->ucmsName;
	}

}
?>