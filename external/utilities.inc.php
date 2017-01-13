<?php

	/**
	 * Pretty-print an array or object
	 * @param  mixed $a Array or object
	 */
	function print_a( $a ) {
		print( '<pre>' );
		print_r( $a );
		print( '</pre>' );
	}

	/**
	 * Convert a shorthand byte value from a PHP configuration directive to an integer value
	 * @param    string   $value
	 * @return   int
	 */
	function convert_bytes( $value ) {
		if ( is_numeric( $value ) ) {
			return $value;
		} else {
			$value_length = strlen( $value );
			$qty = substr( $value, 0, $value_length - 1 );
			$unit = strtolower( substr( $value, $value_length - 1 ) );
			switch ( $unit ) {
				case 'k':
					$qty *= 1024;
					break;
				case 'm':
					$qty *= 1048576;
					break;
				case 'g':
					$qty *= 1073741824;
					break;
			}
			return $qty;
		}
	}

	/**
	 * Get an item from an array/object, or a default value if it's not set
	 * @param  mixed $var      Array or object
	 * @param  mixed $key      Key or index, depending on the array/object
	 * @param  mixed $default  A default value to return if the item it's not in the array/object
	 * @return mixed           The requested item (if present) or the default value
	 */
	function get_item($var, $key, $default = '') {
		return is_object($var) ?
			( isset( $var->$key ) ? $var->$key : $default ) :
			( isset( $var[$key] ) ? $var[$key] : $default );
	}

	/**
	 * Mark an option as selected by evaluating the variable
	 * @param  mixed  $var   Variable expected value
	 * @param  mixed  $val   Variable actual value
	 * @param  string $attr  Attribute to use (selected, checked, etc)
	 * @param  boolean $echo Whether to echo the result or not
	 * @return string        Selected attribute text or an empty text
	 */
	function option_selected($var, $val, $attr = "selected", $echo = true) {
		$ret = ($var == $val) ? "{$attr}=\"{$attr}\"" : '';
		if ($echo) {
			echo $ret;
		}
		return $ret;
	}

	/**
	 * Log something to file
	 * @param  mixed  $data     What to log
	 * @param  string $log_file Log name, without extension
	 * @return nothing
	 */
	function log_to_file($data, $log_file = '') {
		global $site;
		$log_file = $log_file ? $log_file : date('Y-m');
		$file = fopen( $site->baseDir("/log/{$log_file}.log"), 'a');
		$date = date('Y-m-d H:i:s');
		if ( is_array($data) || is_object($data) ) {
			$data = json_encode($data);
		}
		fwrite($file, "{$date} - {$data}\n");
		fclose($file);
	}

	/**
	 * Generate <option> tags for day selection
	 * @param  boolean $selected       The selected day (01-31)
	 * @param  boolean $leading_zeroes Whether to add leading zeroes nor not
	 * @param  boolean $echo           Whether to echo the result or not
	 * @return string                  The generated option tags
	 */
	function select_days($selected = false, $leading_zeroes = true, $echo = true) {
		$ret = '';
		for ($i = 1; $i <= 31; $i++){
			$option_value = str_pad($i, 2, '0', STR_PAD_LEFT);
			$option_text = $leading_zeroes? $option_value : $i;
			$ret .= "<option " . ($selected == $i? 'selected="selected"' : '') . " value=\"{$option_value}\">{$option_text}</option>\n";
		}
		if($echo) echo $ret;
		return $ret;
	}

	/**
	 * Generate <option> tags for month selection
	 * @param  boolean $selected The selected month (01-12)
	 * @param  string  $format   The month format, see the date() function reference on the PHP manual
	 * @param  boolean $echo     Whether to echo the result or not
	 * @return string            The generated option tags
	 */
	function select_months($selected = false, $format = 'm', $echo = true) {
		$ret = '';
		for ($i = 1; $i <= 12; $i++){
			$option_value = str_pad($i, 2, '0', STR_PAD_LEFT);
			$option_text = date( $format, mktime( 0, 0, 0, $i + 1, 0, 0 ) );
			$ret .= "<option " . ($selected == $i? 'selected="selected"' : '') . " value=\"{$option_value}\">{$option_text}</option>\n";
		}
		if($echo) echo $ret;
		return $ret;
	}

	/**
	 * Generate <option> tags for year selection
	 * @param  boolean $selected   The selected year (yyyy format)
	 * @param  boolean $start_year The starting year
	 * @param  integer $num        How many years will be added/subtracted
	 * @param  integer $direction  Whether to add years (1) or subtract them (-1)
	 * @param  boolean $echo       Whether to echo the result or not
	 * @return string              The generated option tags
	 */
	function select_years($selected = false, $start_year = false, $num = 100, $direction = -1, $echo = true) {
		$ret = '';
		$current_year = !$start_year? date('Y') : $start_year;
		for ($i = 0; $i <= $num; $i++){
			$option_value = $current_year + ($i*$direction);
			$option_text = $option_value;
			$ret .= "<option " . ($selected == $i? 'selected="selected"' : '') . " value=\"{$option_value}\">{$option_text}</option>\n";
		}
		if($echo) echo $ret;
		return $ret;
	}

	/**
	 * Convert camelCase to snake_case
	 * @param  string $val Original string
	 * @return string      The converted string
	 */
	function camel_to_snake($val) {
		$val = preg_replace_callback('/[A-Z]/', create_function('$match', 'return "_" . strtolower($match[0]);'), $val);
		return ltrim($val, '_');
	}

	/**
	 * Convert camelCase to dash-case
	 * @param  string $val Original string
	 * @return string      The converted string
	 */
	function camel_to_dash($val) {
		$val = preg_replace_callback('/[A-Z]/', create_function('$match', 'return "-" . strtolower($match[0]);'), $val);
		return ltrim($val, '-');
	}

	/**
	 * Convert snake_case to camelCase
	 * @param  string $val Original string
	 * @return string      The converted string
	 */
	function snake_to_camel($val) {
		$val = str_replace(' ', '', ucwords(str_replace('_', ' ', $val)));
		$val = strtolower(substr($val, 0, 1)).substr($val, 1);
		return $val;
	}

	/**
	 * Build a Google Fonts request string with the given fonts and styles
	 * @param  array $fonts An array with the desired fonts
	 * @param  bool  $echo  Whether to echo the result or not
	 * @return mixed        The well-formed request string or False on error
	 */
	function get_google_fonts($fonts, $echo = false) {
		$ret = false;
		if ($fonts) {
			$parts = array();
			foreach ($fonts as $font => $weight) {
				$font_name = urlencode($font);
				if ( is_array($weight) ) {
					$font_weight = implode(',', $weight);
				} else {
					$font_weight = $weight;
				}
				$parts[] = "{$font_name}:{$font_weight}";
			}
			$params = implode('|', $parts);
			$ret = "//fonts.googleapis.com/css?family={$params}";
			if ($echo) {
				echo $ret;
			}
		}
		return $ret;
	}

?>