<?php
	if(!function_exists('mitterGetBetween')) {
		function mitterGetBetween($pool, $char1="",$char2="") {
			$temp1 = strpos($pool,$char1) + strlen($char1);
			$result = substr($pool,$temp1, strlen($pool));
			$dd=strpos($result,$char2);

			if($dd == 0) {
				$dd = strlen($result);
			}

			return substr($result,0,$dd);
		}
	}

	if(!function_exists('mitterNullFilter')) {
		function mitterNullFilter($var) {
			return ($var !== NULL && $var !== FALSE);
		}
	}

	if(!function_exists('mitterTimeToSeconds')) {
		function mitterTimeToSeconds($str_time) {
			$str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);
			sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
			$time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
			return $time_seconds;
		}
	}

	if(!function_exists('mitterCookieExpireDate')) {
		function mitterCookieExpireDate() {
			return time() + (10 * 365 * 24 * 60 * 60);
		}
	}

	if(!function_exists('mitterFindNestedArrayKey')) {
		function mitterFindNestedArrayKey($array, $key)
		{
			if(array_key_exists($key, $array))
				return $array[$key];

			foreach ($array as $value) {
				if (is_array($value)) {
					$result = findNestedArrayKey($value, $key);

					if($result) {
						return $result;
					}
				}
			}

			return false;
		}
	}

	if(!function_exists('mitterDeepArrayFilter')) {
		function mitterDeepArrayFilter($array)
		{
			$array = array_filter($array, 'nullFilter');

			foreach ($array as $key => $item) {
				if(is_array($item))
					$array[$key] = deepArrayFilter($item);	
			}
			return $array;
		}
	}