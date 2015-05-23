<?php
class DatabaseConnection{
	private static $instance;
	private $dbServer, $dbUser, $dbPassword, $dbName;
	private $tables;
	private $queriesCount;
	private $connection;
	private $prefix;
	private $ucmsName;

	public static function getDefault(){
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
		$this->connection = new PDO("mysql:host=$this->dbServer;port=$this->dbPort;dbname=$this->dbName;charset=utf8", $this->dbUser, $this->dbPassword);
		
		$this->connection->exec("set names utf8");
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//if ($this->connection->connect_errno) {
		//	throw new Exception("Can't connect to database", $this->connection->connect_errno);
		//}
		//if (!$this->connection->set_charset("utf8")) {
		//	throw new Exception("Can't set database charset");
		//}

	}

	public function shutdown(){
		$this->connection = null; 
	}

	public function doQuery($sql, $params = array()){
		if($sql == "") return false;
		$result = $this->connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		if(DEBUG_DISPLAY_QUERY) {
			begin_debug_block();
			echo $sql;
			end_debug_block();
		}
		try{
			$result->execute($params);
			$this->queriesCount++;
		}catch(PDOException $e){
			begin_debug_block();
			echo "<h2>".tr("Query failed")."</h2><br>";
			echo "$sql<br><br>";
			echo $e->getMessage();
			if(UCMS_DEBUG){
				echo "<br><h3>Trace:</h3>".$e->getTraceAsString();
			}
			end_debug_block();
			
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
				$outType = PDO::FETCH_ASSOC;
			break;
			
			default:
				$outType = PDO::FETCH_ASSOC;
			break;
		}

		if( is_object($query) && $query instanceof PDOStatement ){
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

	public function setDefaultTables(){
		$this->tables = array('settings');
	}

	public function getuCMSName(){
		return $this->ucmsName;
	}

}
?>