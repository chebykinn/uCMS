<?php
namespace uCMS\Core\Database;
use uCMS\Core\Debug;
use uCMS\Core\Object;
class Query extends Object implements QueryInterface{
	private $driver = NULL;
	protected $database = NULL;
	protected $sql = "";
	protected $params = [];
	protected $table;
	protected $type;
	protected $fetchType = 'assoc';
	protected $placeholderIndex = 0;

	public function __construct($sql, $params = array(), DatabaseConnection $database = NULL){
		parent::__construct();
		$this->database = $database == NULL ? DatabaseConnection::GetDefault() : $database;
		if( !$this->database->isConnected() ){
			Debug::Log($this->tr("No connection to the database"), Debug::LOG_ERROR, $this);
			return;	
		}
		$driver = $this->database->getDriver();
		$driverClass = __NAMESPACE__."\\Driver\\{$driver->name}\\Query";
		$this->driver = new $driverClass($sql, $params, $this->database);
	}

	public function countRows(){
		return $this->driver->countRows();
	}

	public function insert($columns, $valueLists, $ignore = false){
		return $this->driver->insert($columns, $valueLists, $ignore);
	}

	public function update($columnsAndValues){
		return $this->driver->update($columnsAndValues);
	}

	public function select($columns){
		return $this->driver->select($columns);
	}	

	public function delete(){
		return $this->driver->delete();
	}
	
	public function where(){
		return $this->driver->where();
	}

	public function on(){
		return $this->driver->on();
	}

	public function condition($column, $operator, $value, $required = true){
		return $this->driver->condition($column, $operator, $value, $required);
	}

	public function _and(){
		return $this->driver->_and();
	}

	public function _or(){
		return $this->driver->_or();
	}

	public function left(){
		return $this->driver->left();
	}

	public function right(){
		return $this->driver->right();
	}

	public function inner(){
		return $this->driver->inner();
	}

	public function outer(){
		return $this->driver->outer();
	}

	public function join($table){
		return $this->driver->join($table);
	}

	public function using($column){
		return $this->driver->using($column);
	}

	public function orderBy($columnsAndOrders){
		return $this->driver->orderBy($columnAndOrders);
	}

	public function groupBy($columns, $noQuotes = false){
		return $this->driver->groupBy($columns, $noQuotes);
	}

	public function limit($start, $amount = 0){
		return $this->driver->limit($start, $amount);
	}

	public function __tostring(){
		return (string)$this->driver;
	}

	public function toObject(){
		$this->fetchType = "object";
		return $this;
	}

	public function execute($type = ""){
		return $this->driver->execute($type);
	}

	public function prepare($value){
		return $this->driver->prepare($type);
	}

	public function getTable(){
		return $this->driver->getTable();
	}

	public function createTable(array $schema){
		return $this->driver->createTable($schema);
	}
	
	public function tableExists(){
		return $this->driver->tableExists();
	}

	public function getSQLSize($type, $size, $precision = 0, $scale = 0){
		return $this->driver->getSQLSize($type, $size, $precision, $scale);
	}

	protected function getLocalTableNames($str){
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

	protected function getNextPlaceholder(){
		return ":field_placeholder".$this->placeholderIndex++;
	}
}
?>
