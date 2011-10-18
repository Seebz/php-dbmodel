<?php



/**
 * Classe modèle de base
 */
class Model {
	
	protected $_data = array();
	
	public function __construct($data = array()) {
		$this->update_attributes($data);
	}
	
	
	
	/**
	 * Magical Methods
	 */
	public function __get($name) {
		$method = 'get_' . $name;
		if (method_exists($this, $method)) {
			$value = call_user_func(array($this, $method));
		} else {
			$value = $this->read_attribute($name);
		}
		
		return $value;
	}
	
	public function __set($name, $value = null) {
		$method = 'set_' . $name;
		if (method_exists($this, $method)) {
			$ret = call_user_func(array($this, $method), $value);
		} else {
			$ret = $this->write_attribute($name, $value);
		}
		
		return $ret;
	}
	
	public function __isset($name) {
		return isset($this->_data[$name]);
	}
	
    public function __unset($name) {
		unset($this->_data[$name]);
	}	
	
	public function __toString() {
		$kLength = 0;
		foreach(array_keys($this->_data) as $k) {
			$l = strlen($k);
			if ($l>$kLength) { $kLength = $l; }
		}
		$out = array( sprintf('[%s Model]', get_class($this)) );
		foreach($this->_data as $k => $v) {
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
	public function read_attribute($name) {
		if (isset($this->_data[ $name ])) {
			return $this->_data[ $name ];
		}
	}
	
	public function write_attribute($name, $value = null) {
		return $this->_data[ $name ] = $value;
	}
	
	public function update_attributes($data = array()) {
		foreach($data as $key => $value) {
			$this->{$key} = $value;
		}
	}
	
	public function to_array() {
		return (array) $this->_data;
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
	
}



?>