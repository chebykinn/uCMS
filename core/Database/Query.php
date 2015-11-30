<?php
namespace uCMS\Core\Database;
use uCMS\Core\Debug;
class Query{
	private $database;
	private $sql = "";
	private $params = array();
	private $table;
	private $type;
	private $fetchType = 'assoc';
	private $doCache = false;
	private $needLogicOperator = false;
	private $schemaError = DatabaseConnection::SCHEMA_CORRECT;
	private $placeholderIndex = 0;

	public function __construct($sql, $params = array(), $database = NULL){
		$this->database = DatabaseConnection::GetDatabase($database);
		if( empty($this->database) ){
			Debug::Log(tr("No connection to the database"), Debug::LOG_ERROR);
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
		$this->sql = "SELECT 1 FROM $this->table";
		return $this;
	}

	public function insert($columns, $valueLists, $ignore = false){
		$this->type = 'insert';
		if( is_array($columns) && is_array($valueLists) ){
			$params = [];
			$columnsAmount = count($columns);
			$i = 0;
			$lists = [];
			foreach ($valueLists as $valueList) {
				if( !is_array($valueList) ) return $this;
				if( $columnsAmount == 0 || count($valueList) >= $columnsAmount ){
					$c = 0;
					foreach ($valueList as $value) {
						// Ensure that we won't fall out of bounds.
						if( $columnsAmount > 0 && $c == $columnsAmount ) break;
						$key = $this->getNextPlaceholder();
						$params[$key] = $value;
						$lists[$i][] = $key;
						$c++;
					}
					$columnsAmount = $c;
				}else{
					// If we haven't got enough value, we will fill missing with zeroes.
					$valuesLeft = $columnsAmount - count($valueList);
					for ($c = $valuesLeft; $c < $columnsAmount; $c++) { 
						$valueList[$c] = 0;
					}
				}
				$i++;
			}
			$sqlColumns = '';
			if( !empty($columns) ){
				$sqlColumns = '('.implode(", ", $columns).')';	
			}

			$sqlValues = [];
			foreach ($lists as $values) {
				$sqlValues[] = '('.implode(', ', $values).')';
			}
			$sqlValues  = implode(", ", $sqlValues);
			/**
			* @todo checks
			*/

			$sqlIgnore = $ignore ? "IGNORE" : "";
			$this->sql = "INSERT $sqlIgnore INTO $this->table $sqlColumns VALUES $sqlValues";
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
				$key = $this->getNextPlaceholder();
				$params[$key] = $value;
				$columns[] = $column;
				$values[]  = $value;
				if($sqlUpdate != '') $sqlUpdate .= ', ';
				$sqlUpdate .= "$column = $key";
			}

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

	public function condition($column, $operator, $value, $required = true){
		/**
		* @todo filter operators
		*/
		if( strpos($this->sql, 'WHERE') === false ){
			$this->sql .= ' WHERE';
		}
		if( $this->needLogicOperator ){
			if( $required ){
				$this->sql .= ' AND';
			}else{
				$this->sql .= ' OR';
			}
		}
		$safeName = $this->getNextPlaceholder();
		$this->params[$safeName] = $value;
		if( strpos($column, "{") !== false ){
			// we need to add prefix
			$column = $this->getLocalTableNames($column);
		}
		$condSql = $column.' '.$operator.' '.$safeName;
		$this->sql .= " $condSql";
		$this->needLogicOperator = true;
		return $this;
	}

	public function _and(){
		$this->sql .= ' AND';
		$this->needLogicOperator = false;
		return $this;
	}

	public function _or(){
		$this->sql .= ' OR';
		$this->needLogicOperator = false;
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

	public function orderBy($columnsAndOrders){
		$orderSql = " ORDER BY ";
		$allowed = array("ASC", "DESC", "asc", "desc");
		$sorts = array();
		if( is_array($columnsAndOrders) ){
			foreach ($columnsAndOrders as $column => $order) {
				if( !in_array($order, $allowed) ) continue;
					$sorts[] = $column.' '.$order;
			}

			$orderSql .= implode(", ", $sorts);
			$this->sql .= $orderSql;
		}
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
				$count = $query->rowCount();
				return $count;
			break;

			case 'query':
				$returnValue = $this->database->doQuery($this->sql, $this->params);
			break;
			
			default:
				$returnValue = $this->database->doQuery($this->sql, $this->params);
			break;
		}
		$this->type = "";
		$this->sql = "";
		$this->params = array();
		$this->placeholderIndex = 0;
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

	public function createTable(array $schema){
		$this->type = 'createTable';
		$result = DatabaseConnection::CheckSchema($schema);

		if( $result != DatabaseConnection::SCHEMA_CORRECT ){
			// If we got wrong schema, we should save error code
			$this->schemaError = $result;
			return $this;
		}
		$sql = "CREATE TABLE {$this->table} (\n";
		$i = 0;
		$amount = count($schema['fields']);
		$needIncrement = false;
		$serialSpec = "";

		// Adding fields
		foreach ($schema['fields'] as $name => $data) {
			$increment = '';
			$default = '';
			$precision = 0;
			$scale = 0;
			if( !isset($data['size']) ){
				$data['size'] = 'normal';
			}

			if( $data['type'] == 'numeric' ){
				$precision = intval($data['precision']);
				$scale = intval($data['scale']);
			}

			// This will get correct sql type from generic type
			$type = $this->database->getSQLSize($data['type'], $data['size'], $precision, $scale);
			if( isset($data['length']) && $data['type'] != 'numeric' && $data['type'] != 'text'  && $data['type'] != 'blob' ){
				$length = intval($data['length']);
				if( $data['type'] == 'varchar' ){
					$type = "varchar($length)";
				}else{
					$type .= "($length)";
				}
			}

			// If we want to set auto increment, we should check primary key, and add it, if it's not exists
			if( $data['type'] == 'serial' ){
				$needIncrement = true;
				if( isset($schema['primary key']) && is_array($schema['primary key']) ){
					if( !in_array($name, $schema['primary key']) ){
						$schema['primary key'][] = $name;
					}
				}

				if( !isset($schema['primary key']) ){
					$schema['primary key'] = $name;
				}else{
					if( $schema['primary key'] != $name ){
						$needIncrement = false;
					}
				}
			}

			$notNull = (isset($data['not null']) && (bool)$data['not null']) ? ' NOT NULL' : ''; 
			
			if( isset($data['default']) ){
				$value = $data['default'];
				if( !is_integer($value) ){
					$value = "'$value'";
				}
				$default = " DEFAULT $value";
			}
			$name = preg_replace("/[^a-zA-Z0-9]/i", '', $name);
			$sql .= "$name $type$notNull$default";
			if( $needIncrement && $data['type'] == 'serial' ){
				$serialSpec = "$name $type$notNull$default";
			}
			if( $i+1 < $amount ) $sql .= ",\n";
			$i++;
		}

		$sql .= "\n)";

		$charset = 'utf8';
		if( isset($schema['mysql_character_set']) ){
			$charset = $schema['mysql_character_set'];
		}

		$engine = 'InnoDB';
		if( isset($schema['mysql_engine']) ){
			$engine = $schema['mysql_engine'];
		}

		$sql .= " ENGINE=$engine DEFAULT CHARSET=$charset;\n";
		$keysRegex = "/[^a-zA-Z0-9,.\s]/i";
		if( isset($schema['primary key']) || isset($schema['unique keys']) || isset($schema['indexes']) || $needIncrement ){
			$sql .= "ALTER TABLE {$this->table}\n";
			$isFirst = true;
			if( isset($schema['primary key']) ){
				$key = $schema['primary key'];
				if( is_array($key) ){
					$key = implode(', ', $key);
				}
				$key = preg_replace($keysRegex, '', $key);
				$sql .= "ADD PRIMARY KEY ($key)";
				$isFirst = false;
			}

			if( isset($schema['unique keys']) ){
				foreach ($schema['unique keys'] as $name => $keys) {
					if( is_array($keys) ){
						$keys = implode(', ', $keys);
					}
					$name = preg_replace("/[^a-zA-Z0-9]/i", '', $name);
					$keys = preg_replace($keysRegex, '', $keys);
					$sql .= (!$isFirst ? ",\n" : "")."ADD UNIQUE KEY $name ($keys)";
					$isFirst = false;
				}
			}

			if( isset($schema['indexes']) ){
				foreach ($schema['indexes'] as $name => $keys) {
					if( is_array($keys) ){
						$keys = implode(', ', $keys);
					}
					$name = preg_replace("/[^a-zA-Z0-9]/i", '', $name);
					$keys = preg_replace($keysRegex, '', $keys);
					$sql .= (!$isFirst ? ",\n" : "")."ADD KEY $name ($keys)";
					$isFirst = false;
				}
			}

			if( $needIncrement ){
				$sql .= (!$isFirst ? ",\n" : "")."MODIFY $serialSpec AUTO_INCREMENT, AUTO_INCREMENT=1";
			}

			$sql .= ";\n";
		}
		$this->sql = $sql;
		return $this;
	}


	public function getSchemaError(){
		return $this->schemaError;
	}
	
	public function tableExists(){
		return $this->database->isTableExists($this->table);
	}


	private function getNextPlaceholder(){
		return ":field_placeholder".$this->placeholderIndex++;
	}


}
?>