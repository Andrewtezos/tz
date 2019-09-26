<?php
namespace App\Helpers;

class Http {

	static function buildQuery($params){
		return http_build_query($params);
	}

	static function altBuildQuery($params){
		$query = '';
		foreach($params as $k=>$v){
			if(is_null($v)) continue;
			$v = (array) $v;
			foreach($v as $s) $query.="&$k=$s";
		}
		if($query!='') $query[0] = '?';
		return $query;
	}

	static function cookieBuildQuery($params){
		$query = array();
		foreach($params as $k=>$v) $query[] = "$k=$v";
		return join('; ', $query);
	}

	function filesize($url){
		$sch = parse_url($url, PHP_URL_SCHEME);
		if($sch=="http" || $sch=="https") if(array_key_exists("Content-Length", $headers = get_headers($url, 1))) return $headers["Content-Length"];
		return false;
	}
}
