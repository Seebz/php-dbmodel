<?php



/**
 * Classe modèle de base
 */
class Model {
	
	protected $_data = array();
	
	public function __construct($data = array()) {
		$this->_data = $data;
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





/**
 * Classe modèle utilisant la DB
 */
abstract class DbModel extends Model {
	
	static $table_prefix = null;
	static $table_name   = '';
	static $primary_key  = 'id';
	
	
	// Callbacks
	static $before_validate = array();
	static $after_validate  = array();
	
	static $before_create   = array();
	static $after_create    = array();
	
	static $before_update   = array();
	static $after_update    = array();
	
	static $before_save     = array();
	static $after_save      = array();
	
	static $before_destroy  = array();
	static $after_destroy   = array();
	
	
	// Permettra de savoir si l'objet existe en DB
	protected $_exists = false;
	
	protected static $_table_prefixes = array();
	protected static $_table_names    = array();
	protected static $_table_fields   = array();
	
	
	
	/**
	 * Static methods
	 */
	public static function table_prefix() {
		$class_name = get_called_class();
		if (!isset(static::$_table_prefixes[ $class_name ])) {
			static::$_table_prefixes[ $class_name ] = static::$table_prefix ?: 
					DB::getPrefix();
		}
		return static::$_table_prefixes[ $class_name ];
	}
	
	public static function table_name() {
		$class_name = get_called_class();
		if (!isset(static::$_table_names[ $class_name ])) {
			static::$_table_names[ $class_name ] = static::$table_name ?: 
					Inflector::tableize($class_name);
		}
		return static::table_prefix() . static::$_table_names[ $class_name ];
	}
	
	public static function primary_key() {
		return static::$primary_key;
	}
	
	public static function table_fields() {
		$class_name = get_called_class();
		if (!isset(static::$_table_fields[ $class_name ])) {
			$query = sprintf('DESCRIBE %s', static::table_name());
			
			$result = DB::query($query);
			foreach($result as $f) {
				$fields[] = $f['Field'];
			}
			static::$_table_fields[ $class_name ] = $fields;
		}
		return static::$_table_fields[ $class_name ];
	}
	
	public static function get($id) {
		return static::first(array(
			'conditions' => sprintf("%s = '%s'",
					static::primary_key(),
					DB::escape($id)
				),
		));
	}
	
	public static function count($options = array()) {
		$default = array(
			'fields' => static::primary_key(),
		);
		$options = $options + $default;
		if (stripos($options['fields'], 'count') === false) {
			$options['fields'] = "COUNT({$options['fields']})";
		}
		
		$ret = static::first($options);
		return (int) $ret->{$options['fields']};
	}
	
	public static function find($options = array()) {
		$default = array(
			'fields'     => '*',
			'source'     => static::table_name(),
			'join'       => '',
			'conditions' => '1',
			'groupby'    => null,
			'sort'       => 'NULL',
			'offset'     => null,
			'page'       => 1,
		);
		$options = $options + $default;
		
		$join = (is_array($options['join']) ? implode("\n", $options['join']) : $options['join']);
		$groupby = (is_null($options['groupby']) ? '' : "GROUP BY {$options['groupby']}");
		
		$query = sprintf('SELECT %s FROM %s %s WHERE %s %s ORDER BY %s',
			(!is_string($options['fields']) ? implode(', ', $options['fields']) : $options['fields']),
			$options['source'],
			$join,
			static::_prepare_conditions($options['conditions']),
			$groupby,
			$options['sort']
		);
		if ($options['offset'] && is_numeric($options['offset'])) {
			$query .= sprintf(' LIMIT %d,%d',
				(($options['page']-1) * $options['offset']),
				$options['offset']
			);
		}
		$ret = DB::query($query);
		
		$class_name = get_called_class();
		if ($class_name) {
			foreach($ret as &$r) {
				$r = new $class_name($r, true);
			}
		}
		
		return $ret;
	}
	
	public static function first($options = array()) {
		$options['offset'] = 1;
		$options['page']   = 1;
		
		$ret = static::find($options);
		
		if (is_array($ret) && count($ret)) {
			return $ret[0];
		}
	}
	public static function find_first($options = array()) {
		return static::first($options);
	}
	
	
	protected static function _prepare_conditions($conditions) {
		if (is_array($conditions)) {
			$out = array();
			foreach($conditions as $c) {
				$out[] = call_user_func(__METHOD__, $c);
			}
			return implode(' AND ', $conditions);
		}
		
		return "({$conditions})";
	}
	
	
	
	/**
	 * Magical methods
	 */
	public function __construct($data = array(), $exists = false) {
		parent::__construct($data);
		$this->_exists = $exists;
	}
	
	
	
	/**
	 * Getters/Setters
	 */
	public function get_pk() {
		return $this->_data[ static::primary_key() ];
	}
	
	public function get_id() {
		return $this->get_pk();
	}
	
	
	
	/**
	 * Instance methods
	 */
	public function validate() {
		
		$this->_run_callback('before_validate');
		
		$ret = $this->is_valid();
		
		$this->_run_callback('after_validate');
		
		return $ret;
	}
	
	public function save($validate = true) {
		if ($validate && !$this->validate()) {
			return false;
		}
		
		$this->_run_callback('before_save');
		
		if ($this->_exists) {
			$ret = $this->update(false);
		} else {
			$ret = $this->create(false);
		}
		
		$this->_run_callback('after_save');
		
		return $ret;
	}
	
	public function create($validate = true) {
		if ($validate && !$this->validate()) {
			return false;
		}
		$table_fields = static::table_fields();
		
		$this->_run_callback('before_create');
		
		if (in_array('created_at', $table_fields)) {
			$this->_data['created_at'] = date('Y-m-d H:i:s');
		}
		if (in_array('updated_at', $table_fields)) {
			$this->_data['updated_at'] = date('Y-m-d H:i:s');
		}
		
		foreach ($this->_data as $f=>$v) {
			if (in_array($f, $table_fields)) {
				$fields[] = $f;
				$values[] = DB::escape($v);
			}
		}
		if (count($fields)) {
			$query = sprintf('INSERT INTO %s (%s) VALUES (%s)',
				static::table_name(),
				implode(', ', $fields),
				"'" . implode("', '", $values) . "'"
			);
			
			$ret = DB::query($query);
			if ($ret) {
				$this->_data[ static::primary_key() ] = $ret;
				$this->_exists = true;
			}
		}
		
		$this->_run_callback('after_create');
		
		return (isset($ret) ? $ret : null);
	}
	
	public function update($validate = true) {
		if ($validate && !$this->validate()) {
			return false;
		}
		$table_fields = static::table_fields();
		
		$this->_run_callback('before_update');
		
		if (in_array('updated_at', $table_fields)) {
			$this->_data['updated_at'] = date('Y-m-d H:i:s');
		}
		
		foreach ($this->_data as $f=>$v) {
			if (in_array($f, $table_fields)) {
				$sets[] = sprintf("%s='%s'", $f, DB::escape($v));
			}
		}
		if (count($sets)) {
			$query = sprintf("UPDATE %s SET %s WHERE %s='%s'",
				static::table_name(),
				implode(', ', $sets),
				static::primary_key(),
				$this->get_pk()
			);
			$ret = DB::query($query);
		}
		
		$this->_run_callback('after_update');
		
		return (isset($ret) ? $ret : null);
	}
	
	public function destroy() {
		
		$this->_run_callback('before_destroy');
		
		$ret = sprintf("DELETE FROM %s WHERE %s='%s'",
				static::table_name(),
				static::primary_key(),
				$this->get_pk()
		);
		
		$this->_run_callback('after_destroy');
		
		return DB::query($ret);
	}
	
	
	
	/**
	 * Protected Methods
	 */
	protected function _run_callback($name) {
		$callbacks = (array) static::${$name};
		foreach($callbacks as $callback) {
			if (is_callable($callback)) {
				call_user_func($callback, $this);
			} elseif (is_string($callback) && method_exists($this, $callback)) {
				call_user_func(array($this, $callback), $this);
			} else {
				$class  = get_class($this);
				$method = end(explode('_', $name));
				throw new Exception("Callback method `{$callback}` not found for `{$class}::{$method}` !");
			}
		}
	}
	
}


?>