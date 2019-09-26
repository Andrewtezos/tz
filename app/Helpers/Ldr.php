<?php
namespace App\Helpers;

use App\Helpers\Cls;

Class Ldr extends Cls {
	static $outfiles = array();
	static function http($opts){
		if(is_string($opts)) $opts = array('url'=>$opts);
		$hnd = self::http_getHnd($opts);
		$result = curl_exec($hnd);
		if($e = curl_error($hnd)){
			trigger_error('['.date('Y-m-d H:i:s')."] {$e} on {$opts['url']}", E_USER_NOTICE);
			$result = null;
		}
		curl_close($hnd);

		self::http_releaseOutfile(@$opts['outfile']);

		return $result;
	}

	static function http_multi($per_loads, $common=array()){
		$res = array();
		if($per_loads){
			$loads = array();
			foreach($per_loads as $k=>$opts) $loads[$k]['opts'] = array_merge(array('attempts'=>1), $common, is_string($opts)?array('url'=>$opts):$opts);

			$attempt = 0;
			while($loads){
				$attempt++;
				$mhnd = curl_multi_init();
				$hnds = array();
				foreach($loads as $k=>&$load){
					$load['hnd'] = self::http_getHnd($load['opts']);
					curl_multi_add_handle($mhnd, $load['hnd']);
				}
				unset($load);

				$running = null;
				do {
					curl_multi_exec($mhnd, $running);
					usleep(60000);
				} while($running>0);

				foreach(Arrayset::keys($loads) as $k){
					$load = $loads[$k];
					if($e = curl_error($load['hnd'])) {
                                                trigger_error('['.date("Y-m-d H:i:s")."] {$e} on {$load['opts']['url']}", E_USER_NOTICE);
                                        }
					if(!$e || $attempt>=$load['opts']['attempts']){
						$res[$k] = json_decode(curl_multi_getcontent($load['hnd']));
						curl_multi_remove_handle($mhnd, $load['hnd']);
						unset($loads[$k]);
					}
					self::http_releaseOutfile(@$load['opts']['outfile']);
				}

				curl_multi_close($mhnd);
			}
		}
		return $res;
	}

	static function http_releaseOutfile($filename){
		if($filename!='' && isset(self::$outfiles[$filename])){
			@fclose(self::$outfiles[$filename]);
			unset(self::$outfiles[$filename]);
		}
	}

	static function http_getHnd($opts=array()){
		static $def = array(
			'useragent'=>'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko)',
			'returnheaders'=>false,
			'ssl_verifypeer'=>false,
			'followlocation'=>true,
			'maxredirs'=>5,
			'timeout'=>120,
			'connecttimeout'=>120,
			//'tor'=>true,
			//'proxy'=>array('85.12.229.169',8081),
		);
		$opts = array_merge($def, Arrayset::removeNulls($opts));
		$hnd = curl_init();
		if(isset($opts['cookies'])) curl_setopt($hnd, CURLOPT_COOKIE, Http::cookieBuildQuery($opts['cookies']));
		if(isset($opts['headers'])){
			foreach(array_keys($opts['headers']) as $k) if(is_numeric($k)){
				if(is_array($opts['headers'][$k])) $opts['headers'][] = join(': ', $opts['headers'][$k]);
				else $opts['headers'][] = $opts['headers'][$k];
			} else {
				$opts['headers'][] = $k.': '.$opts['headers'][$k];
				unset($opts['headers'][$k]);
			}
			curl_setopt($hnd, CURLOPT_HTTPHEADER, $opts['headers']);
		}
		if(isset($opts['referer'])) curl_setopt($hnd, CURLOPT_REFERER, $opts['referer']);
		if(isset($opts['auth'])) curl_setopt($hnd, CURLOPT_USERPWD, "{$opts['auth'][0]}:{$opts['auth'][1]}");
		if(isset($opts['post'])){
			curl_setopt($hnd, CURLOPT_POST, true);
			curl_setopt($hnd, CURLOPT_POSTFIELDS, $opts['post']);
		} else {
			curl_setopt($hnd, CURLOPT_HTTPGET, true);
		}
		if(isset($opts['get']) && count($opts['get'])){
			$opts['url'].= (strpos($opts['url'], '?')===false?'?':'&').Http::buildQuery($opts['get']);
		}
		curl_setopt($hnd, CURLOPT_URL, $opts['url']);
		curl_setopt($hnd, CURLOPT_HEADER, $opts['returnheaders']);
		curl_setopt($hnd, CURLOPT_USERAGENT, $opts['useragent']);
		curl_setopt($hnd, CURLOPT_SSL_VERIFYPEER, $opts['ssl_verifypeer']);
		curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, $opts['followlocation']);
		curl_setopt($hnd, CURLOPT_MAXREDIRS, $opts['maxredirs']);
		curl_setopt($hnd, CURLOPT_CONNECTTIMEOUT, $opts['connecttimeout']);
		curl_setopt($hnd, CURLOPT_TIMEOUT, $opts['timeout']);
		if(isset($opts['outfile'])){
			self::$outfiles[$opts['outfile']] = @fopen($opts['outfile'], "w");
			curl_setopt($hnd, CURLOPT_FILE, self::$outfiles[$opts['outfile']]);
		} else {
			curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
		}
		if(isset($opts['cookie'])){
			curl_setopt($hnd, CURLOPT_COOKIEJAR, $opts['cookie']);
			curl_setopt($hnd, CURLOPT_COOKIEFILE, $opts['cookie']);
		}
        if(isset($opts['tor'])){
                //curl_setopt($hnd, CURLOPT_HTTPPROXYTUNNEL, TRUE);
                curl_setopt($hnd, CURLOPT_PROXY, '127.0.0.1:9050');
                curl_setopt($hnd, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        if(isset($opts['proxy']) && $opts['proxy']!==false){
                //if($opts['proxy'][1]==3128) curl_setopt($hnd, CURLOPT_HTTPPROXYTUNNEL, TRUE);
                if($opts['proxy'][1]==8081) curl_setopt($hnd, CURLOPT_HTTPPROXYTUNNEL, TRUE);
                curl_setopt($hnd, CURLOPT_PROXY, $opts['proxy'][0].':'.$opts['proxy'][1]);
                //if($opts['proxy'][1]==3128) curl_setopt($hnd, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
		return $hnd;
	}

}
