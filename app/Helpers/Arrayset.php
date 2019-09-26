<?php
namespace App\Helpers;

class Arrayset{
	var $maxIndex = -1;
	function remove($k){return $this->offsetUnset($k);}
	function add($v){return $this->append($v);}
	function append($v){return $this->offsetSet(NULL, $v);}
	function offsetSet($k, $v){
		if(isset($k) && is_numeric($k) && $k>$this->maxIndex) $this->maxIndex = $k;
		if(!isset($k)) $k = ++$this->maxIndex;
		parent::offsetSet($k, $v);
		return $k;
	}
	static function keys($array){
		return array_keys($array);
	}
	static function removeNulls($array){
		reset($array);
		foreach ($array as $k => $v) if(is_null($v)) unset($array[$k]);
		return $array;
	}
}
