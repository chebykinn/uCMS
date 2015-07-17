<?php
namespace uCMS\Core\Database;
use uCMS\Core;
class Query{
	private $database;
	private $sql;
	private $params = array();
	private $table;
	private $type;
	private $fetchType = 'assoc';
	private $doCache = false;

	public function __construct($sql, $params = array(), $database = NULL){
		$this->database = DatabaseConnection::GetDatabase($database);
		if( empty($this->database) ){
			Debug::Log(tr("No connection to the database"), UC_LOG_ERROR);
			return;
		}
		$outType = array();
		$sql = $this->getLocalTableNames($sql);
		if( !preg_match("/select|insert|update|delete/i", $sql, $outType) ){
			$this->table = $sql;
		}else{
			$this->sql = $sql;	
			$this->type = strtolower($outType[0]);
			$this->params = $params;
		}
	}

	public function doCache(){
		$this->doCache = true;
	}

	public function getLocalTableNames($str){
		$tables = array();
		if( preg_match_all("/{(.*?)}/i", $str, $tables) ){
			foreach ($tables[1] as &$value){
    			$value = $this->database->getPrefix().$value;
			}
			$pattern = array_fill(0, count($tables[1]), "/{(.*?)}/i");
			$str = preg_replace($pattern, $tables[1], $str, 1);
		}
		return $str;
	}

	public function countRows(){
		$this->type = "count";
		$this->sql = "SELECT COUNT(*) as count FROM $this->table";
		return $this;
	}

	public function insert($columnsAndValues){
		$this->type = 'insert';
		$noQuotes = array('NULL', 'NOW()');
		if( is_array($columnsAndValues) ){
			//$columns = array_keys($columnsAndValues);
			//$values = array_values($columnsAndValues);
			$params = array();
			$columns = array();
			foreach ($columnsAndValues as $column => $value) {
				$params[":$column"] = $value;
				$columns[] = $column;
			}
			$sqlColumns = implode(", ", $columns);
			$sqlValues  = implode(", ", array_keys($params));
			/**
			* @todo checks
			*/

		//	foreach ($values as &$value) {
				//if( !in_array($value, $noQuotes) ){
				//	$value = "'$value'";
				//}
		//	}

			$this->sql = "INSERT INTO $this->table ($sqlColumns) VALUES ($sqlValues)";
			$this->params = $params;
		}
		return $this;
	}

	public function update($columnsAndValues){
		$this->type = 'update';
		$noQuotes = array('NULL', 'NOW()');
		if( is_array($columnsAndValues) ){
			$params = array();
			$columns = array();
			$values = array();

			$sqlUpdate = "";
			foreach ($columnsAndValues as $column => $value) {
				$params[":$column"] = $value;
				$columns[] = $column;
				$values[]  = $value;
				if($sqlUpdate != '') $sqlUpdate .= ', ';
				$sqlUpdate .= "$column = :$column";
			}

			/*$sqlUpdate = "";
			foreach ($columnsAndValues as $column => $value) {
				$column = '`'.$column.'`';
				if( !in_array($value, $noQuotes) ){
					$value = "'$value'";
				}
				if($sqlUpdate != '') $sqlUpdate .= ', ';
				$sqlUpdate .= "$column = $value";
			}*/
			$this->sql = "UPDATE $this->table SET $sqlUpdate";
			$this->params = $params;
		}
		return $this;
	}

	public function select($columns){
		$this->type = 'select';
		if(is_array($columns)){
			foreach ($columns as &$column) {
				$column = $this->getLocalTableNames($column);
			}
			$sqlColumns = implode(", ", $columns);
		}else{
			$columns = $this->getLocalTableNames($columns);
			$sqlColumns = "$columns";
		}
		$this->sql = "SELECT $sqlColumns FROM $this->table";
		return $this;
	}	

	public function delete(){
		$this->type = 'delete';
		$this->sql = "DELETE FROM $this->table";
		return $this;
	}
	
	public function where(){
		if( strpos($this->sql, 'WHERE') === false ){
			$this->sql .= ' WHERE';
		}
		return $this;
	}

	public function on(){
		$this->sql .= ' ON';
		return $this;
	}

	public function condition($column, $operator, $value){
		/**
		* @todo filter operators
		*/
		$safeName = $this->findNextName($column);
		$this->params[$safeName] = $value;
		$condSql = $column.' '.$operator.' '.$safeName;
		$this->sql .= " $condSql";
		return $this;
	}

	public function _and(){
		$this->sql .= ' AND';
		return $this;
	}

	public function _or(){
		$this->sql .= ' OR';
		return $this;
	}

	public function left(){
		$this->sql .= ' LEFT';
		return $this;
	}

	public function right(){
		$this->sql .= ' RIGHT';
		return $this;
	}

	public function inner(){
		$this->sql .= ' INNER';
		return $this;
	}

	public function outer(){
		$this->sql .= ' OUTER';
		return $this;
	}

	public function join($table){
		$table = $this->getLocalTableNames($table);
		$this->sql .= " JOIN `$table`";
		return $this;
	}

	public function using($column){
		$this->sql .= " USING(`$column`)";
		return $this;
	}

	public function orderBy($columns, $orders){
		$orderSql = " ORDER BY ";
		if( is_array($columns) && is_array($orders) ){
			for ($i = 0; $i < count($columns); $i++) { 
				$orderSql .= '`'.$columns[$i].'` '.$orders[$i];
				if($i+1 < count($columns)) $orderSql .= ', ';
			}
		}else{
			$orderSql .= '`'.$columns.'` '.$orders;
		}
		$this->sql .= $orderSql;
		return $this;
	}

	public function groupBy($columns, $noQuotes = false){
		$this->sql .= " GROUP BY ";
		if( is_array($columns) ){
			$sqlColumns = '`'.implode("`, `", $columns).'`';
		}else{
			if($noQuotes){
				$sqlColumns = $columns;
			}else{
				$sqlColumns = "`$columns`";
			}
		}
		$this->sql .= $sqlColumns;
		return $this;
	}

	public function limit($start, $amount = 0){
		if( strpos($this->sql, 'LIMIT') === false ){
			if($amount != 0){
				$this->sql .= ' LIMIT '.intval($start).', '.intval($amount); //check
			}else{
				$this->sql .= ' LIMIT '.intval($start); //check
			}
		}
		return $this;
	}

	public function __tostring(){
		return $this->sql;
	}

	public function toObject(){
		$this->fetchType = "object";
		return $this;
	}

	public function execute($type = ""){
		if( empty($this->database) ) return;
		if( empty($type) ) $type = $this->type;
		$returnValue = NULL;
		switch ($type) {
			case 'select':
				$data = array();
				$query = $this->database->doQuery($this->sql, $this->params);

				$i = 0;
				while($row = $this->database->fetch($query, $this->fetchType)){
					$data[$i] = $row;
					$i++;
				}
				$returnValue = $data;
			break;

			case 'count':
				$query = $this->database->doQuery($this->sql, $this->params);
				$row = $this->database->fetch($query, $this->fetchType);
				if( !empty($row['count']) ) return intval($row['count']);
			break;
			
			default:
				$returnValue = $this->database->doQuery($this->sql, $this->params);
			break;
		}
		$this->type = "";
		$this->sql = "";
		$this->params = array();
		if( is_null($returnValue) ) $returnValue = $this->database->doQuery($this->sql, $this->params);
		return $returnValue;
	}

	public function prepare($value){
		if( empty($this->database) ) return;
		if(is_array($value)){
			for($i = 0; $i < count($value); $i++){
				$value[$i] = $this->database->escapeString($value[$i]);
			}
		}else{
			$value = $this->database->escapeString($value);
		}
		return $value;
	}

	public function getTable(){
		if( empty($this->database) ) return;
		return $this->table;
	}

	public function findNextName($column, $index = 0){
		$column = preg_replace("/[^a-z0-9.]/i", "", $column);
		$name = $index == 0 ? ":$column" : ":$column$index";
		if( isset($this->params[$name]) ){
			$index++;
			$this->findNextName(":$column$index", $index);
		}
		return $name;
	}
}
?>