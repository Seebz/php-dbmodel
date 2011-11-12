<?php


/**
 * Classe modèle de base
 */
class Model implements ArrayAccess {
	
	protected $_vars = array();
	
	
	/**
	 * Magical Methods
	 */
	public function __construct(array $vars = array()) {
		$this->update_attributes($vars);
	}
	
	function __clone() {
		foreach ($this as $key => $val) {
			if (is_object($val) || (is_array($val))) {
				$this->{$key} = unserialize(serialize($val));
			}
		}
	}
	
	
	public function & __get($key) {
		$var = & $this->_read_attribute($key); return $var;
	}
	
	public function __set($key, $value) {
		$setter = 'set_' . $key;
		if ( method_exists($this, $setter) ) {
			return $this->$setter($value);
		} else {
			return $this->_vars[ $key ] = $value;
		}
	}
	
	public function __isset($key) {
		return isset($this->_vars[ $key ]);
	}
	
	public function __unset($key) {
		unset($this->_vars[ $key ]);
	}
	
	
	public function & offsetGet($key) {
		// You can return by reference in offsetGet as of PHP 5.3.4
		$var = & $this->_read_attribute($key); return $var;
	}
	
	public function offsetSet($key, $value) {
		return $this->__set($key, $value);
	}
	
	public function offsetExists($key) {
		return $this->__isset($key);
	}
	
	public function offsetUnset($key) {
		return $this->__unset($key);
	}
	
	
	public function __toString() {
		$kLength = 0;
		foreach(array_keys($this->_vars) as $k) {
			$l = strlen($k);
			if ($l>$kLength) { $kLength = $l; }
		}
		$out = array( sprintf('[%s Model]', get_class($this)) );
		foreach($this->_vars as $k => $v) {
			if ($v === true)	$v = 'True';
			if ($v === false)	$v = 'False';
			if ($v === null)	$v = 'Null';
			if (is_object($v))
				$v = '[Object '. get_class($v) .']';
			$out[] = sprintf("%-{$kLength}s : %s", $k, $v);
		}
		$out[] = '';
		
		return implode(PHP_EOL, $out);
	}
	
	
	
	/**
	 * Usefull Methods
	 */
	public function & read_attribute($name) {
		if (isset($this->_vars[ $name ])) {
			$var = & $this->_vars[ $name ]; return $var;
		}
		$var = null; return $var;
	}
	
	public function write_attribute($name, $value = null) {
		return $this->_vars[ $name ] = $value;
	}
	
	public function update_attributes($vars = array()) {
		foreach($vars as $key => $value) {
			$this->{$key} = $value;
		}
	}
	
	public function to_array() {
		return (array) $this->_vars;
	}
	
	public function to_json() {
		return json_encode($this->to_array());
	}
	
	
	
	/**
	 * Validations
	 */
	static $validations = array();
	
	protected $_validator = null;
	protected function _getValidator() {
		if (is_null($this->_validator)) {
			$this->_validator = new Validator($this);
		}
		return $this->_validator;
	}
	
	public function is_valid() {
		return $this->_getValidator()->is_valid();
	}
	
	public function is_invalid() {
		return !$this->is_valid();
	}
	
	public function get_errors() {
		return $this->_getValidator()->get_errors();
	}
	
	
	/**
	 * Misc
	 */
	protected function & _read_attribute($key) {
		$getter = 'get_' . $key;
		if ( method_exists($this, $getter) ) {
			$var = $this->$getter($key); return $var;
		} elseif ( isset($this->_vars[ $key ]) ) {
			$var = & $this->read_attribute($key); return $var;
		}
		$this->_trigger_error(
			sprintf('Undefined property: %s::$%s', get_class($this), $key),
			E_USER_NOTICE
		);
		$var = null; return $var;
	}
	
	
	protected function _trigger_error($msg, $type = E_USER_NOTICE, $file = null, $line = null) {
		if ( ($type === E_USER_ERROR && !(error_reporting() & E_ERROR))
			|| ($type === E_USER_WARNING && !(error_reporting() & E_WARNING))
			|| ($type === E_USER_NOTICE && !(error_reporting() & E_NOTICE))
			|| ($type === E_USER_DEPRECATED && !(error_reporting() & E_WARNING)) ) {
			return;
		}
		$debug  = debug_backtrace();
		//$callee = next($debug);
		$callee = $debug[2];
		
		if (is_null($file)) {
			$file = $callee['file'];
		}
		if (is_null($line)) {
			$line = $callee['line'];
		}
		$msg .= sprintf(' in %s on line %d', $file, $line);
		
		set_error_handler(array($this, '_error_handler'), $type);
		trigger_error($msg, $type);
		restore_error_handler();
	}
	
	protected function _error_handler($errno, $errstr) {
		switch($errno) {
			case E_USER_ERROR:      $errtype = 'Error';      break;
			case E_USER_WARNING:    $errtype = 'Warning';    break;
			case E_USER_NOTICE:     $errtype = 'Notice';     break;
			case E_USER_DEPRECATED: $errtype = 'Deprecated'; break;
			default:                $errtype = 'Unknown';
		}
		if (ini_get('display_errors')) {
			printf("<br />\n<b>%s</b>: %s<br /><br />\n", $errtype, $errstr);
		}
		if (ini_get('log_errors')) {
			error_log(sprintf('PHP %s:  %s', $errtype, $errstr));
		}
		return true;
	}
	
}



?>