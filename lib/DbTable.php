<?php



/**
 * Classe d'interactions avec les Tables
 */
class DbTable {
	
	static protected $_instances = array();
	
	protected $_class_name = null;
	
	protected $_prefix      = null;
	protected $_name        = null;
	protected $_primary_key = null;
	protected $_description = null;
	protected $_defaults    = null;
	
	
	static public function load($class_name) {
		if (!isset(self::$_instances[ $class_name ])) {
			$self = get_called_class();
			self::$_instances[ $class_name ] = new $self($class_name);
		}
		return self::$_instances[ $class_name ];
	}
	
	
	public function __construct($class_name) {
		$this->_class_name = $class_name;
		
		$this->_prefix = ( $class_name::$table_prefix ?: DB::getPrefix() );
		$this->_name   = $this->_prefix .
		                    ( $class_name::$table_name ?: Inflector::tableize($class_name) );
		$this->_primary_key = $class_name::$primary_key;
	}
	
	
	public function prefix() {
		return $this->_prefix;
	}
	
	public function name() {
		return $this->_name;
	}
	
	public function primary_key() {
		return $this->_primary_key;
	}
	
	public function description() {
		if (is_null($this->_description)) {
			$query = sprintf('DESCRIBE %s', $this->name());
			$results = DB::query($query);
			
			$this->_description = array();
			foreach($results as $r) {
				$this->_description[ $r['Field'] ] = $r;
			}
		}
		return $this->_description;
	}
	
	public function fields() {
		return array_keys($this->description());
	}
	
	public function defaults() {
		if (is_null($this->_defaults)) {
			$description = $this->description();
			
			$this->_defaults = array();
			foreach($description as $d) {
				$this->_defaults[ $d['Field'] ] = $d['Default'];
			}
		}
		return $this->_defaults;
	}
	
}



?>