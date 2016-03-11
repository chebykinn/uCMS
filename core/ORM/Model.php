<?php
/**
* uCMS ORM Model.
*
* @author Ivan Chebykin
* @author ivan4b69@gmail.com
* @since 2.0
*/
namespace uCMS\Core\ORM;
use uCMS\Core\Database\Query;
use uCMS\Core\Object;
/**
* This class provides ORM Model for uCMS.
* 
* This class is used in uCMS ORM implementation.
*/
abstract class Model extends Object{
	/**
	* @var string $primaryKey Contains ORM table primaryKey.
	*/
	private $primaryKey;
	/**
	* @var string $tableName Contains ORM table name.
	*/
	private $tableName;
	/**
	* @var string $tableAlias Optional alias for table.
	*/
	private $tableAlias;
	/**
	* @var array $associations Associations with other models.
	*/
	private $associations = array();
	/**
	* @var bool $modified A flag to determine if model data was changed after loading from database.
	*/
	private $modified = false;
	/**
	* @var bool $exists A flag to determine if row is present in database.
	*/
	private $exists = false;
	/**
	* @var string $name The name of model.
	*/
	public static $models;

	final public function __construct($owner = NULL){
		parent::__construct($owner);
		// self::$models[$this->name] = $this->name; // TODO: consider store associations here
		$this->init();
	}

	/**
	* Initialize model.
	*
	* Abstract method that is usually used by models to set up tables and associations.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	abstract public function init();

	/**
	* Get or set table name.
	*
	* @since 2.0
	* @param string $name New name for table.
	* @return string Table name.
	*/
	final public function tableName($name = NULL){
		if ( empty($this->tableName) || $name ){
			$this->tableName = $name ? $name : strtolower($this->_name);
		}
		return $this->tableName;
	}

	/**
	* Get or set table alias.
	*
	* @since 2.0
	* @param string $alias New alias for table.
	* @return string Table alias.
	*/
	final public function tableAlias($alias = NULL){
		if ( $alias ){
			$this->tableAlias = $alias;
		}
		return $this->tableAlias;
	}

	/**
	* Get or set primary key.
	*
	* @since 2.0
	* @param string $key New key for table.
	* @return string Table primary key.
	*/
	final public function primaryKey($key = NULL){
		if ( empty($this->primaryKey) || $key ){
			$this->primaryKey = $key ? $key : 'id';
		}
		return $this->primaryKey;
	}

	/**
	* Add owner association.
	*
	* Add model to which this class belongs to.
	*
	* @since 2.0
	* @param string $modelName The name of associated model.
	* @param array $options Additional options for association.
	* @return void
	*/
	final public function belongsTo($modelName, $options = NULL){
		$this->associations[$modelName] = array(
			'name' => $modelName,
			'type' => 'belongsTo',
			'options' => $options
		);
	}

	/**
	* Add owned association.
	*
	* Add model which this class owns.
	*
	* @since 2.0
	* @param string $modelName The name of associated model.
	* @param array $options Additional options for association.
	* @return void
	*/
	final public function hasMany($modelName, $options = NULL){
		$this->associations[$modelName] = array(
			'name' => $modelName,
			'type' => 'hasMany',
			'options' => $options
		);
	}

	/**
	* Get associations.
	*
	* Get all assigned associations for given model or for all models.
	*
	* @since 2.0
	* @param string $modelName The name of associated model.
	* @return array Assigned associations.
	*/
	final public function getAssociations($modelName = ""){
		if( isset($this->associations[$modelName]) ){
			return $this->associations[$modelName];
		}
		return $this->associations;
	}

	/**
	* Get binding for variable.
	*
	* Check if given variable name is bound to some of the associations and get class which is it bound to.
	*
	* @since 2.0
	* @param string $varName The name of bound variable
	* @return array Association for binding if found, empty array if not
	*/
	final public function GetBinding($varName){
		foreach ($this->getAssociations() as $name => $association) {
			if( isset($association['options']['bind']) && $association['options']['bind'] === $varName ){
				return $association;
			}
		}
		return array();
	}

	/**
	* Get array of ORM rows.
	*
	* Process results from database query to an array of prepared Row classes.
	*
	* @since 2.0
	* @param \PDOStatement $results Executed query results. 
	* @return array Processed rows.
	*/
	final private function processResults($results){
		$processed = array();
		if( empty($results) ) return [];
		$rowCount = $results->rowCount();
		if( $rowCount > 0 ){
			while ($row = $results->fetch(\PDO::FETCH_OBJ) ) {
				$processed[] = $this->processRow($row);
			}
		}
		return $processed;
	}

	/**
	* Create ORM Row.
	*
	* Create object of Row from given data and current model.
	*
	* @since 2.0
	* @param object $row Data for row
	* @return Row Processed row for current model
	*/
	final private function processRow($row){
		return new Row($this, $row);
	}

	final public function setModified(){
		$this->modified = true;
	}

	final public function find($conditions = array()){
		$usedKeys = array('columns', 'where', 'start', 'limit', 'orders', 'noOrder');
		// If we got a number we will try to select row by ID
		if( is_numeric($conditions) || is_string($conditions) ){
			$id = is_numeric($conditions) ? intval($conditions) : $conditions;
			$conditions = array();
			$conditions['limit'] = 1;
			$conditions['noOrder'] = true;
			$conditions['where'][0] = [$this->primaryKey(), '=', $id];
		}

		// Creating query
		$query = new Query('{'.$this->tableName().'}');
		$start = NULL;
		// TODO: Consider set default limit ?
		$limit = NULL;
		$columns = '*';
		$where = array();
		if( !isset($conditions['orders']) ){
			$orders[$this->primaryKey()] = 'DESC';
		}

		if( isset($conditions['columns']) ){
			$columns = $conditions['columns'];
		}
		$query = $query->select($columns);


		if( isset($conditions['where']) ){
			$where = $conditions['where'];
			$amount = count($where);
			$i = 0;
			foreach ($where as $condition) {
				if( isset($condition[0]) && isset($condition[1]) && isset($condition[2]) ){
					$query = $query->condition($condition[0], $condition[1], $condition[2]);
				}

				if( isset($condition[3]) && $i+1 < $amount ){
					if( $condition[3] === 'and' ){
						$query = $query->_and();
					}

					if( $condition[3] === 'or' ){
						$query = $query->_or();
					}
				}
				$i++;
			}
		}

		foreach ($conditions as $key => $value) {
			if( !in_array($key, $usedKeys) && !is_array($value) ){ // should be is scalar ?
				$query->condition($key, '=', $value);
			}
		}

		if( isset($conditions['start']) ){
			$start = intval($conditions['start']);
		}

		if( isset($conditions['limit']) ){
			$limit = intval($conditions['limit']);
			// if( $limit === 1 && $start === NULL ){
			// 	$conditions['noOrder'] = true;
			// }
		}

		if( isset($conditions['orders']) ){
			$orders = $conditions['orders'];
		}

		if( !isset($conditions['noOrder']) ){
			$query = $query->orderBy($orders);
		}

		if( $limit != NULL ){
			if( $start != NULL ){
				$query = $query->limit($start, $limit);
			}else{
				$query = $query->limit($limit);
			}
		}
		
		$results = $query->execute('query');
		$processed = $this->processResults($results);

		if( empty($processed) ){
			if( $limit === 1 ){
				return NULL;
			}
			return array();
		}
		$this->exists = true;
		if( $limit === 1 ){
			return $processed[0];
		}
		return $processed;
	}

	final public function last($conditions = array()){
		$last = $this->find(array('limit' => 1));
		return $last;
	}

	final public function first($conditions = array()){
		$first = $this->find(array('limit' => 1, 'orders' => array($this->primaryKey() => 'ASC')));
		return $first;
	}

	final public function count($conditions = array()){
		$conditions['noOrder'] = true;
		$conditions['columns'] = 'COUNT({'.$this->tableName().'}.'.$this->primaryKey().') as count';
		$results = $this->find($conditions);
		if( !empty($results) ){
			$row = $results[0];
			$count = $row->count;
			return $count;
		}
		return 0;
	}

	public function create($row){
		if( !$this->exists && $this->modified ){
			$data = $row->getColumns();
			$columns = array_keys($data);
			$values = array_values($data);
			$query = new Query('{'.$this->tableName().'}');
			$key = $this->primaryKey();
			if( isset($data[$key]) ){
				$check = $query->select($key)->condition($key, '=', $data[$key])->execute('count');
				if( $check > 0 ) return false; // Prevent from adding duplicate entry 
			}
			$result = $query->insert($columns, [$values])->execute();
			return $result;
		}
		return false;
	}

	public function update($row){
		if( $this->exists && $this->modified ){
			$data = $row->getColumns();
			$query = new Query('{'.$this->tableName().'}');
			$key = $this->primaryKey();
			if( !isset($data[$key]) ) return false;

			// If model key was changed, old value will be stored in _oldkey
			if( isset($row->_oldkey) ){
				$id = $row->_oldkey;
				unset($data['_oldkey']);
			}else{
				$id = $data[$key];
			}

			$result = $query->update($data)->condition($key, '=', $id)->execute();
			return $result;
		}
		return false;
	}

	public function delete($row){
		if( $this->exists ){
			$key = $this->primaryKey();
			$query = new Query('{'.$this->tableName().'}');
			$result = $query->delete()->condition($key, '=', $row->$key)->execute();
			return $result;
		}
		return false;
	}

	final public function emptyRow(){
		return $this->processRow($this, []);
	}
}
?>
