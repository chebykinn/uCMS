<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Object;
class Term extends Object{
	public static function FromArray($data, $prefixes = array(), $namespaces = array(), $returnClass = "\\uCMS\\Core\\Extensions\\Entries\\Term"){
		
		$term = parent::FromArray($data, $prefixes, $namespaces, $returnClass);
		return $term;
	}
}
?>