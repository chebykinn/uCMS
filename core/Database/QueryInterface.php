<?php
namespace uCMS\Core\Database;
interface QueryInterface{
	public function countRows();

	public function insert($columns, $valueLists, $ignore = false);

	public function update($columnsAndValues);

	public function select($columns);

	public function delete();
	
	public function where();

	public function on();

	public function condition($column, $operator, $value, $required = true);

	public function _and();

	public function _or();

	public function left();

	public function right();

	public function inner();

	public function outer();

	public function join($table);

	public function using($column);

	public function orderBy($columnsAndOrders);

	public function groupBy($columns, $noQuotes = false);

	public function limit($start, $amount = 0);

	public function createTable(array $schema);

	public function getTable();

	public function getSQLSize($type, $size, $precision = 0, $scale = 0);
}
?>
