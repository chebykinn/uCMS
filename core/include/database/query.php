<?php
class Query{
	private $database;
	private $sql;
	private $table;

	public function __construct(){
		$args = func_get_args();
		$this->sql = "";
		$this->database = uCMS::getInstance()->getDatabase();
		if( empty($this->database) ){
			log_add("No connection to the database", UC_LOG_ERROR);
			return;
		}
		if( count($args) == 1 ){ // $table
			$table = $args[0];
			$outTable = array();
			if( preg_match("/{(.*?)}/", $table, $outTable) ){
				$this->table = $this->database->getPrefix().$this->prepare($outTable[1]);
			}else{
				$this->table = $this->prepare($table);
			}
		}else{
			$sql = "";
			$check = !empty($args[0]) ? (bool) $args[0] : false;
			if( $check && !empty($args[1]) ) $sql = $args[1];
			$tables = array();
			if( preg_match_all("/{(.*?)}/i", $sql, $tables) ){
				foreach ($tables[1] as &$value){
    				$value = $this->database->getPrefix().$this->prepare($value);
				}
				$pattern = array_fill(0, count($tables[1]), "/{(.*?)}/i");
				$sql = preg_replace($pattern, $tables[1], $sql, 1);
			}
			if( isset($args[2]) && is_array($args[2]) ){
				$words = array_keys($args[2]);
				$replacements = array_values($args[2]);
				foreach ($replacements as &$value) {
					$value = $this->prepare($value);
				}
				$sql = str_replace($words, $replacements, $sql);
			}
			$this->sql = $sql;
		}
	}

	public function insert($columnsAndValues){
		$noQuotes = array('NULL', 'NOW()');
		if( is_array($columnsAndValues) ){
			$columns = $this->prepare( array_keys($columnsAndValues) );
			$values = $this->prepare( array_values($columnsAndValues) );
			/**
			* @todo checks
			*/
			$sqlColumns = "`".implode("`, `", $columns)."`";
			foreach ($values as &$value) {
				if( !in_array($value, $noQuotes) ){
					$value = "'$value'";
				}
			}
			$sqlValues = implode(", ", $values);
			$this->sql = "INSERT INTO `$this->table` ($sqlColumns) VALUES ($sqlValues)";
		}
		return $this;
	}

	public function update($columnsAndValues){
		$noQuotes = array('NULL', 'NOW()');
		if( is_array($columnsAndValues) ){
			
			$sqlUpdate = "";
			foreach ($columnsAndValues as $column => $value) {
				$column = '`'.$this->prepare( $column ).'`';
				$value = $this->prepare( $value );
				if( !in_array($value, $noQuotes) ){
					$value = "'$value'";
				}
				if($sqlUpdate != '') $sqlUpdate .= ', ';
				$sqlUpdate .= "$column = $value";
			}
			$this->sql = "UPDATE `$this->table` SET $sqlUpdate";
		}
		return $this;
	}

	public function select($columns, $noQuotes = false){
		$columns = $this->prepare($columns);
		if(is_array($columns)){
			$sqlColumns = "`".implode("`, `", $columns)."`";
		}else{
			if(!$noQuotes){
				$sqlColumns = "`$columns`";
			}else{
				$sqlColumns = "$columns";
			}
		}
		$this->sql = "SELECT $sqlColumns FROM `$this->table`";
		return $this;
	}	

	public function delete(){
		$this->sql = "DELETE FROM `$this->table`";
		return $this;
	}
	
	public function where(){
		if( mb_strpos($this->sql, 'WHERE') === false ){
			$this->sql .= ' WHERE';
		}
		return $this;
	}

	public function on(){
		$this->sql .= ' ON';
		return $this;
	}

	public function condition($column, $operator, $value, $noQuotes = false){
		$column = $this->prepare($column);
		$operator = $this->prepare($operator);
		$value = $this->prepare($value);
		/**
		* @todo filter operators
		*/
		if(!$noQuotes){
			$condSql = '`'.$column.'` '.$operator.' \''.$value.'\'';
		}else{
			$condSql = $column.' '.$operator.' '.$value;
		}
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

	public function leftJoin($table){
		return $this;
	}

	public function rightJoin($table){
		return $this;
	}

	public function innerJoin($table){
		return $this;
	}

	public function using($column){
		$column = $this->prepare($column);
		$this->sql .= " USING(`$column`)";
		return $this;
	}

	public function orderBy($columns, $orders){
		$columns = $this->prepare($columns);
		$orders = $this->prepare($orders);
		$orderSql = " ORDER BY ";
		if(is_array($columns) && is_array($orders)){
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

	public function groupBy($columns){
		return $this;
	}

	public function limit($start, $amount = 0){
		if( mb_strpos($this->sql, 'LIMIT') === false ){
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

	public function execute(){
		if( empty($this->database) ) return;
		if(true){ //select
			$query = $this->database->doQuery($this->sql);
			$data = array();
			$i = 0;
			while($row = $this->database->fetchArray($query)){
				$data[$i] = $row;
				$i++;
			}
			return $data;
		}
		return $this->database->doQuery($this->sql);
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
}
?>