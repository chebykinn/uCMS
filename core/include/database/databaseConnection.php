<?php
class DatabaseConnection{
	private $dbServer, $dbUser, $dbPassword, $dbName;
	private $queriesCount;
	private $connection;
	private $prefix;

	public function __construct($server, $login, $password, $dbName, $prefix){
		$this->dbServer = $server;
		$this->dbUser = $login;
		$this->dbPassword = $password;
		$this->dbName = $dbName;
		$this->prefix = $prefix;
		$this->connect();
	}

	public function connect(){	
		$this->connection = @mysqli_connect($this->dbServer, $this->dbUser, $this->dbPassword, $this->dbName);
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

}
?>