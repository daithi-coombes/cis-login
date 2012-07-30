<?php

/**
 * @package david-coombes
 */
/**
 * Debug function. Prints an array wrapped inside &lt;pre> tags for easy viewing in 
 * html browser. Will print the array variable name as well, this is taken from 
 * debug dump using preg_match. 
 * 
 * @param array $ar the array to print 
 * @link http://www.david-coombes.com 
 * @copyright open 
 */
if (!function_exists("ar_print")) {

	function ar_print($ar) {

		//vars  
		$name = "";
		$caller_info = array_shift(debug_backtrace());
		$lines = file($caller_info['file']);
		$line = $lines[$caller_info['line'] - 1];

		//search debug dump for var name  
		if (preg_match('/ar_print\\s*\\(\$(\\w+)/', $line, $matches))
			$name = $matches[1];

		//print to stdout  
		print "\n<pre>\n";
		print "{$name}\t";
		print_r($ar);
		print "\n</pre>\n";
	}

}

/**
 * Debug function. Prints debug_print_backtrace() between two pre tags. 
 * 
 * @link http://www.david-coombes.com 
 * @copyright open 
 */
if (!function_exists("debug_print")) {

	function debug_print() {

		print "<pre>\n";
		debug_print_backtrace();
		print "</pre>\n";
	}

}


if (!function_exists("log_file")) {

	/**
	 * Logs a string to a file
	 *
	 * @param string $str 
	 */
	function log_file($str, $path = false, $mode = 'a') {

		//if array then turn to printable string (not serialized)
		if (is_array($str))
			$str = ar_to_string($str);

		($path) ? $file = "{$path}/log.txt\n" : $file = "log.txt\n";

		$fp = fopen($file, $mode);
		fwrite($fp, "$str\n");
		fclose($fp);
	}

}

if (!function_exists("ar_to_string")) {

	/**
	 * Converts an array to readable string and returns. Tries to mimic the
	 * format of print_r including newline and tab chars.
	 * 
	 * @param array $ar The array to convert
	 * @param integer $tab Default 1. The number of tabs for this line.
	 * @return type string
	 */
	function ar_to_string($ar, $tab = 1) {

		$str = "";
		$tabs = "";

		for ($x = 0; $x < $tab; $x++)
			$tabs .= "\t";

		foreach ($ar as $key => $val) {
			if (is_array($ar[$key]))
				$str .= ar_to_string($ar, ($tab + 1));
			else
				$str = "{$tabs}{$key}\t=>\t{$val}\n";
		}

		return $str;
	}

}

if (!function_exists("rand_md5")) {

	/**
	 * Generate a random string.
	 *
	 * @param integer $length Default 11. The length of the returned string.
	 * @return string 
	 */
	function rand_md5($length=11) {
		$max = ceil($length / 32);
		$random = '';
		for ($i = 0; $i < $max; $i++) {
			$random .= md5(microtime(true) . mt_rand(10000, 90000));
		}
		return substr($random, 0, $length);
	}

}

if (!function_exists("url_query_append")){
	
	/**
	 * Append params to a url.
	 * 
	 * @param string $url The full url including current params.
	 * @param array $vars Associative array of param name=>value pairs.
	 * @return string 
	 */
	function url_query_append( $url, $vars=array()){
		
		//vars
		$url = parse_url($url);
		$query_vars = array();
		parse_str($url['query'], $query_vars);
		$query_vars = array_merge($query_vars, $vars);
		
		//build and return new string
		return "{$url['scheme']}://{$url['host']}{$url['path']}"
			. "?" . http_build_query($query_vars);
	}
}
?>
