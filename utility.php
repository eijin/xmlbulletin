<?php
/*
* This is written by Eiji Nakai, but it is from many good people's ideas.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

	$trans_from = array();
	$trans_to   = array();
	
	/* INITIALIZING */
	
	// search locale table
	$lang_env = preg_split('/,/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$d = dir("./");
	while (false !== ($entry = $d->read())) {
		 list($fname, $ext) = preg_split("/\./",$entry);
		 if ($fname == 'locale') {
			 foreach ($lang_env as $lenv) {
				 if (strpos($lenv, $ext) === 0) {
					 loadLocaleFile($entry);
				 }
			 }
		 }
	}
	$d->close();
	
	function loadLocaleFile($entry) {
		global $trans_from;
		global $trans_to;
		$trans_from = array();
		$trans_to = array();
		if (($fp = fopen($entry, "r")) !== false) {
			while (($data = fgetcsv($fp, 3000, "=")) !== false) {
				$trans_from[] = trim($data[0]);
				$trans_to[] = trim($data[1]);
			}
			fclose($fp);
		}
	}
	
	
	/* UTILITIES */
	
	/*
	* transration
	* $str is vsprintf() format string
	* you can put valuables or array  as arguments (both acceptable)
	*/
	function __($str, $params=NULL) {
		global $trans_from;
		global $trans_to;
		if(func_num_args() > 1) {
			if (!is_array($params)) {
				$args = func_get_args();
				array_shift($args);
				$params = array_values($args);
			}
		}
		if($key = array_search($str, $trans_from)) {
			$out_str = $trans_to[$key];
		} else {
			$out_str = $str;
		}
		return vsprintf($out_str, $params);
	}
		
	
	/*
	* hash object
	*/
	function hashObject($obj) {
		$res = obj2hash($obj);
		$s = serialize($res);
		return hash('md5', $s);
	}

	function obj2hash($object) {
			if(is_object($object)) {
					$list=get_object_vars($object);
					while(list($k,$v)=each($list)) {
							$res[$k]=obj2hash($v);
					}
			} else if(is_array($object)) {
					while(list($k,$v)=each($object)) {
							$res[$k]=obj2hash($v);
					}
			} else {
					return $object;
			}
			return $res;
	}
	
	/*
	* validation helper
	*   -- Thanks for various sites opening these codes to the public.
	*/
	function validate($rule, $param, $val) {
		switch($rule) {
			case "require":
				if (empty($val)) { return 'REQUIRE_ERROR'; }
				break;
			case "email":
				$domain_check = ($param == "checkDNS" ) ? true : false;
				if (!empty($val) && !check_email($val, $domain_check)) {
					return 'EMAIL_CHECK_ERROR';
				}
				break;
			case "zip":
				switch($param) {
					case "":	// no value is 'jp'
					case "jp": $ptn = '/^\d{3}-?\d{4}$/'; break;
					case "us": $ptn = "/^([0-9]{5})(-[0-9]{4})?$/i"; break;
					default: return "VALIDATE_TYPE_ERROR";
				}
				if (!empty($val) && !preg_match($ptn, $val)) { return "ZIPCODE_INCORRECT"; }
				break;
			case "phone":
				switch($param) {
					case "":	// no value is 'jp'
					case "jp": $ptn = '/^\d{2,5}-?\d{1,5}-?\d{3,5}$/'; break;
					case "us": $ptn = '/\(?\d{3}\)?[-\s.]?\d{3}[-\s.]\d{4}/x';; break;
					default: return "VALIDATE_TYPE_ERROR";
				}
				if (!empty($val) && !preg_match($ptn, $val)) { return "PHONE_INCORRECT"; }
				break;
			case "date":
				if(empty($param)) { $param = "Y-m-d"; }
				if(!empty($val)) {
					$d = strtotime($val);
					$dstr = date($param,$d);
					if ($val != $dstr) { return "DATE_INCORRECT"; }
				}
				break;
			case "max":
				$val = intval($val); $param = intval($param);
				if (!empty($val) && $val > $param) { return "OVER_MAX"; }
				break;
			case "min":
				$val = intval($val); $param = intval($param);
				if (!empty($val) && $val < $param) { return "UNDER_MIN"; }
				break;
			case "maxLength":
				$param = intval($param);
				if (!empty($val) && strlen($val) > $param) { return "OVER_LENGTH"; }
				break;
			case "minLength":
				$param = intval($param);
				if (!empty($val) && strlen($val) < $param) { return "SHORT_LENGTH"; }
				break;
			//case "zenKatakana":
			//	if(!empty($val) && !preg_match('/^[ァ-ヶー]+$/',$val)) { return "NOT_ZENKATAKANA"; }
			//	break;
			case "alphaNumeric":
				if (!empty($val) && !preg_match('/^[a-zA-Z0-9 \_\-\+]+$/', $val)) { return "ALPHANUMERI_INCORRECT"; }
				break;
			case "numeric":
				if (!empty($val) && !preg_match('/^[0-9]+$/', $val)) { return "NUMERIC_INCORRECT"; }
				break;
			case "url":
				if (!empty($val) && !preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $val)) {
					return "URL_INCORRECT";
				}
				break;
			case "ip":
				if (!empty($val) && !preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $val)) {
					return "IP_INCORRECT";
				}
				break;
			case "creditCard":		// credit card number
				if (!empty($val) && !preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/', $val)) {
					return "CREDIT_CARD_INCORRECT";
				}
			case "ssn":		// u.s. social security number
				if (!empty($val) && !preg_match('/^[\d]{3}-[\d]{2}-[\d]{4}$/', $val)) {
					return "SSN_INCORRECT";
				}
				break;
			case "custom":
				if (!empty($val)) {
					if (!preg_match($val, $val)) { return "CUSTOM_INCORRECT"; }
				}
				break;
			default:
				return "VALIDATE_TYPE_ERROR";
		}
		return "OK";
	}
	
	function check_email($email, $domainCheck = false) {
		if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $email)) {
				if ($domainCheck && function_exists('checkdnsrr')) {
						list (, $domain)  = explode('@', $email);
						if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
								return true;
						}
						return false;
				}
				return true;
		}
		return false;
	}

?>
