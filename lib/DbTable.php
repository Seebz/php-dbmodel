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
		$this->_name   = $this->_prefix . ( $class_name::$table_name ?: Inflector::tableize($class_name) );
		$this->_primary_key = $class_name::$primary_key;
	}
	
	
	
	/**
	 * Informations Methods
	 */
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
	
	
	
	/**
	 * Finder Methods
	 */
	public function find(array $options = array()) {
		$defaults = array(
			'fields'     => '*',
			'source'     => $this->name(),
			'join'       => '',
			'conditions' => '1',
			'groupby'    => null,
			'having'     => null,
			'sort'       => 'NULL',
			'offset'     => null,
			'page'       => 1,
		);
		$options = $options + $defaults;
		
		$fields     = $this->_fields($options['fields']);
		$source     = $this->_source($options['source']);
		$join       = $this->_join($options['join']);
		$conditions = $this->_conditions($options['conditions']);
		$groupby    = $this->_groupby($options['groupby']);
		$having     = $this->_having($options['having']);
		$sort       = $this->_sort($options['sort']);
		$limit      = $this->_limit($options['offset'], $options['page']);
		
		$query = sprintf('SELECT %s FROM %s%s WHERE %s%s%s ORDER BY %s%s',
			$fields, $source, $join, $conditions, $groupby, $having, $sort, $limit
		);
		$ret = DB::query($query);
		
		foreach($ret as &$r) {
			$r = new $this->_class_name($r, true);
		}
		
		return $ret;
	}
	
	
	protected function _fields($fields) {
		if (is_array($fields)) {
			return implode(', ', array_map(__METHOD__, $fields));
		}
		return $fields;
	}
	
	protected function _source($source) {
		if (is_array($source)) {
			return implode(', ', array_map(__METHOD__, $source));
		}
		return $source;
	}
	
	protected function _join($join) {
		if (empty($join)) {
			return '';
		} elseif (is_array($join)) {
			return implode(' ', array_map(__METHOD__, $join));
		}
		return ' ' . $join;
	}
	
	protected function _conditions($conditions) {
		if (is_array($conditions)) {
			$out = array();
			foreach($conditions as $k => $v) {
				if (is_int($k)) {
					$out[] = call_user_func(__METHOD__, $conditions);
				} elseif (!is_array($v)) {
					$out[] = sprintf('%s = %s', $k, $this->_escape_field_value($k, $v));
				} elseif (!empty($v)) {
					foreach($v as &$c) {
						$c = $this->_escape_field_value($k, $c);
					}
					$out[] = sprintf('%s IN (%s)', $k, implode(', ', $v));
				} else {
					$out[] = 'false';
				}
			}
			return implode(' AND ', $out);
		} elseif (preg_match('`[\s](OR|AND)[\s]`i', (string) $conditions)) {
			return "({$conditions})";
		} else {
			return $conditions;
		}
	}
	
	protected function _groupby($groupby, $level = 0) {
		if (empty($groupby)) {
			return '';
		} elseif (is_array($groupby)) {
			return implode(', ', array_map(__METHOD__, $groupby, $level+1));
		}
		return ($level ? $groupby : " GROUP BY {$groupby}");
	}
	
	protected function _having($having) {
		// TODO
		return $having;
	}
	
	protected function _sort($sort) {
		if (is_array($sort)) {
			return implode(', ', array_map(__METHOD__, $sort));
		}
		return $sort;
	}
	
	protected function _limit($offset, $page) {
		if (!$offset) {
			return '';
		}
		$page = max(0, $page);
		return sprintf(' LIMIT %d,%d', (($page-1) * $offset), $offset);
	}
	
	
	protected function _escape_field_value($field_name, $value) {
		// TODO
		return "'" . DB::escape($value) . "'";
	}
	
}



?>