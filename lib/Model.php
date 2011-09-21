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
			$ret = call_user_func(array($this, $method, $value));
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
	
	public function to_array() {
		return (array) $this->_data;
	}
	
	public function to_json() {
		return json_encode($this->to_array());
	}
	
}





/**
 * Classe modèle utilisant la DB
 */
class DbModel extends Model {
	
	static $table_name  = null;
	static $primary_key = 'id'; 
	
	
	// Callbacks
	static $before_create  = array();
	static $after_create   = array();
	
	static $before_update  = array();
	static $after_update   = array();
	
	static $before_save    = array();
	static $after_save     = array();
	
	static $before_destroy = array();
	static $after_destroy  = array();
	
	
	// Contiendra les champs existants en DB
	protected static $_table_fields = array();
	// Permettra de savoir si l'objet existe en DB
	protected $_exists = false;
	

	/*
	 * Static methods
	 */
	public static function table_name() {
		if (is_null(static::$table_name)) {
			static::$table_name = Inflector::tableize(get_called_class());
		}
		return DB::getPrefix() . static::$table_name;
	}
	
	public static function table_fields() {
		if (empty(static::$_table_fields)) {
			$query = sprintf('DESCRIBE %s', static::table_name());
			
			$result = DB::query($query);
			foreach($result as $f) {
				$fields[] = $f['Field'];
			}
			static::$_table_fields = $fields;
		}
		return static::$_table_fields;
	}
	
	public static function get($id) {
		return self::find_first(array(
			'conditions' => sprintf("%s = '%s'",
					static::$primary_key,
					DB::escape($id)
				),
		));
	}
	
	public static function find($options = array()) {
		$default = array(
			'fields'     => '*',
			'source'     => static::table_name(),
			'conditions' => '1',
			'sort'       => 'NULL',
			'offset'     => null,
			'page'       => 1,
		);
		$options = $options + $default;
		
		$query = sprintf('SELECT %s FROM %s WHERE %s ORDER BY %s',
			(!is_string($options['fields']) ? implode(', ', $options['fields']) : $options['fields']),
			$options['source'],
			self::_prepare_conditions($options['conditions']),
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
	
	public static function find_first($options = array()) {
		$options['offset'] = 1;
		$options['page']   = 1;
		
		$ret = static::find($options);
		
		if (is_array($ret) && count($ret)) {
			return $ret[0];
		}
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
	
	
	/*
	 * Magical methods
	 */
	public function __construct($data = array(), $exists = false) {
		parent::__construct($data);
		$this->_exists = $exists;
	}
	
	
	
	/*
	 * Instance methods
	 */
	public function getPkValue() {
		return $this->_data[ static::$primary_key ];
	}
	
	public function save() {
		
		$this->_run_callback('before_save');
		
		if ($this->_exists) {
			$ret = $this->update();
		} else {
			$ret = $this->create();
		}
		
		$this->_run_callback('after_save');
		
		return $ret;
	}
	
	public function create() {
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
				$this->_data[ static::$primary_key ] = $ret;
				$this->_exists = true;
			}
		}
		
		$this->_run_callback('after_create');
		
		return (isset($ret) ? $ret : null);
	}
	
	public function update() {
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
				static::$primary_key,
				$this->getPkValue()
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
				static::$primary_key,
				$this->getPkValue()
		);
		
		$this->_run_callback('after_destroy');
		
		return DB::query($ret);
	}
	
	
	
	/*
	 * Protected Methods
	 */
	protected function _run_callback($name) {
		$callbacks = (array) static::${$name};
		foreach($callbacks as $callback) {
			if (is_callable($callback)) {
				call_user_func($callback, $this);
			} elseif (is_string($callback) && method_exists($this, $callback)) {
				$this->{$callback}($this);
			} else {
				$class  = get_class($this);
				$method = end(explode('_', $name));
				throw new Exception("Callback method `{$callback}` not found for `{$class}::{$method}` !");
			}
		}
	}
	
}


?>