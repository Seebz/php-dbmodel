<?php


/**
 * Classe modèle utilisant la DB
 */
abstract class DbModel extends Model {
	
	static $table_prefix = null;
	static $table_name   = '';
	static $primary_key  = 'id';
	static $connection   = 'default';
	
	
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
	
	
	
	/**
	 * Static Informations Methods
	 */
	static public function table() {
		return DbTable::load( get_called_class() );
	}
	
	static public function table_prefix() {
		return static::table()->prefix();
	}
	
	static public function table_name() {
		return static::table()->name();
	}
	
	static public function primary_key() {
		return static::table()->primary_key();
	}
	
	static public function table_fields() {
		return static::table()->fields();
	}
	
	static public function defaults_values() {
		return static::table()->defaults();
	}
	
	static public function connection() {
		return static::table()->connection();
	}
	
	
	
	/**
	 * Finder Methods
	 */
	static public function find($what = 'all', array $options = array()) {
		return static::table()->find($what, $options);
	}
	
	static public function all(array $options = array()) {
		return static::find('all', $options);
	}
	
	static public function first(array $options = array()) {
		return static::find('first', $options);
	}
	
	static public function last(array $options = array()) {
		return static::find('last', $options);
	}
	
	static public function get($id) {
		return static::find('first', array(
			'conditions' => array(static::primary_key() => $id),
		));
	}
	
	static public function count(array $options = array()) {
		return static::table()->count($options);
	}
	
	
	
	/**
	 * Magical Methods
	 */
	public function __construct(array $vars = array(), $exists = false) {
		$this->_vars = self::table()->defaults();
		$this->update_attributes($vars);
		$this->_exists = $exists;
	}
	
	public function serialize() {
		return serialize(array(
			'vars'   => $this->_vars,
			'exists' => $this->_exists,
		));
	}
	
	public function unserialize($data) {
		$data = unserialize($data);
		$this->_vars   = $data['vars'];
		$this->_exists = $data['exists'];
	}
	
	
	
	/**
	 * Getters/Setters
	 */
	public function get_pk() {
		return $this->read_attribute( static::primary_key() );
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
		
		$this->_run_callback('before_create');
		
		$table_fields = static::table_fields();
		if (in_array('created_at', $table_fields)) {
			$this->_vars['created_at'] = date('Y-m-d H:i:s');
		}
		if (in_array('updated_at', $table_fields)) {
			$this->_vars['updated_at'] = date('Y-m-d H:i:s');
		}
		
		$id = static::table()->create($this->_vars);
		if ($id) {
			$this->_vars[ static::primary_key() ] = $id;
			$this->_exists = true;
		}
		
		$this->_run_callback('after_create');
		
		return (isset($id) ? $id : null);
	}
	
	public function update($validate = true) {
		if ($validate && !$this->validate()) {
			return false;
		}
		
		$this->_run_callback('before_update');
		
		$table_fields = static::table_fields();
		if (in_array('updated_at', $table_fields)) {
			$this->_vars['updated_at'] = date('Y-m-d H:i:s');
		}
		
		$ret = static::table()->update($this->pk, $this->_vars);
		
		$this->_run_callback('after_update');
		
		return (isset($ret) ? $ret : null);
	}
	
	public function destroy() {
		
		$this->_run_callback('before_destroy');
		
		$ret = static::table()->destroy($this->pk);
		
		$this->_run_callback('after_destroy');
		
		return $ret;
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