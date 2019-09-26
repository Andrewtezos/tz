<?php
namespace App\Helpers;

class Cls {

	var $events = array();

	static $__isSingletone = false;

	function getEvent($eventName){
		if(!isset($this->events[$eventName])) $this->events[$eventName] = new Event($this, $eventName);
		return $this->events[$eventName];
	}

	function on($eventName, $function){
		$e = $this->getEvent($eventName);
		return $e->addListener($function);
	}

	function event($eventName, $attrs=array()){
		$parts = explode(':', $eventName);
		$prevented = false;
		while(count($parts)>0){
			$name = join(':', $parts);
			if(isset($this->events[$name])){
				$pre = $this->events[$name]->fire($attrs);
				$prevented|= $pre;
			}
			array_pop($parts);
		}
		return !$prevented;
	}

	function callFn($func, $args = array()){
		return Fn::call(array($this, $func), $args);
	}

	function fn($func){
		if(isset($this)) return new Fn(array($this, $func));
		else return new Fn(array(static::getClassName(), $func));
	}

	function sta($fname){
		return $this->getClassName().'::sta__'.$fname;
	}

	static function __callStatic($name, $arguments){
		if(substr($name, 0, 5)=='sta__') return static::inst()->callFn(str_replace('sta__', '', $name), $arguments);
		return null;
	}

	function getClassName(){
		return get_called_class();
	}

	static function inst(){
		return X(static::getClassName());
	}

	function chain(&$result, $func){
		$result = $this->callFn($func, arrayWorker::sub(func_get_args(), 2));
		return $this;
	}
}
