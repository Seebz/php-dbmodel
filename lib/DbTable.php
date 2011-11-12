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
	 * CRUD Methods
	 */
	public function find($what = 'all', array $options = array()) {
		$defaults = array(
			'fields'     => '*',
			'source'     => sprintf('%s AS %s', $this->name(), $this->_class_name),
			'join'       => '',
			'conditions' => true,
			'groupby'    => null,
			'having'     => null,
			'sort'       => null,
			'limit'      => null,
			'page'       => 1,
		);
		$options = $options + $defaults;
		
		switch($what) {
			case 'first':
				$options['limit'] = 1;
				$options['page']  = 1;
				break;
			case 'last':
				$options['limit'] = 1;
				$options['page']  = 1;
				if ($options['sort']) {
					$sort = $this->_sort($options['sort']);
					$sort = preg_replace('`[\s]DESC`i', '!asc!', $sort);
					$sort = preg_replace('`[\s]ASC`i', ' DESC', $sort);
					$sort = str_replace('!asc!', ' ASC', $sort);
					$options['sort'] = $sort;
				} else {
					$options['sort'] = '-' . $this->primary_key();
				}
				break;
		}
		
		$fields     = $this->_fields($options['fields']);
		$source     = $this->_source($options['source']);
		$join       = $this->_join($options['join']);
		$conditions = $this->_conditions($options['conditions']);
		$groupby    = $this->_groupby($options['groupby']);
		$having     = $this->_having($options['having']);
		$sort       = $this->_sort($options['sort']);
		$limit      = $this->_limit($options['limit'], $options['page']);
		
		$query = sprintf('SELECT %s FROM %s%s WHERE %s%s%s ORDER BY %s%s',
			$fields, $source, $join, $conditions, $groupby, $having, $sort, $limit
		);
		$ret = DB::query($query);
		
		foreach($ret as &$r) {
			$r = new $this->_class_name($r, true);
		}
		
		switch($what) {
			case 'first':
			case 'last':
				return (is_array($ret) && count($ret) ? $ret[0] : $ret);
				break;
			default:
				return $ret;
		}
	}
	
	public function count(array $options = array()) {
		$default = array(
			'fields' => $this->primary_key(),
		);
		$options = $options + $default;
		
		if (stripos($options['fields'], 'count') === false) {
			$options['fields'] = "COUNT({$options['fields']})";
		}
		
		$ret = $this->find('first', $options);
		return (int) $ret->{$options['fields']};
	}
	
	public function create($data = array()) {
		$table_fields = $this->fields();
		
		foreach ($data as $f => $v) {
			if (in_array($f, $table_fields)) {
				$fields[] = $f;
				$values[] = $this->_escape_field_value($f, $v);
			}
		}
		
		$query = sprintf('INSERT INTO %s (%s) VALUES (%s)',
			$this->name(), implode(', ', $fields), implode(', ', $values)
		);
		
		return DB::query($query);
	}
	
	public function update($pk_value, $data = array()) {
		$table_fields = $this->fields();
		$pk_field     = $this->primary_key();
		
		foreach ($data as $f => $v) {
			if (in_array($f, $table_fields) && $f != $pk_field) {
				$sets[] = $this->_conditions(array($f => $v));
			}
		}
		$condition = $this->_conditions(array($pk_field => $pk_value));
		
		$query = sprintf('UPDATE %s SET %s WHERE %s',
			$this->name(), implode(', ', $sets), $condition
		);
		return DB::query($query);
	}
	
	public function destroy($pk_value) {
		$pk_field  = $this->primary_key();
		$condition = $this->_conditions(array($pk_field => $pk_value));
		
		$query = sprintf('DELETE FROM %s WHERE %s',
			$this->name(), $condition
		);
		return DB::query($query);
	}
	
	
	
	/**
	 * Protected Methods
	 */
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
					foreach($v as $i => $c) {
						$v[$i] = $this->_escape_field_value($k, $c);
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
		if (!$sort) {
			return 'NULL';
		}
		if (is_string($sort) && strpos($sort, ',') !== false) {
			$sort = explode(',', $sort);
		}
		if (is_array($sort)) {
			$sort = array_map('trim', $sort);
			$sort = array_filter($sort);
			return implode(', ', array_map(__METHOD__, $sort));
		} elseif (!preg_match('`[\s](ASC|DESC)`i', $sort)) {
			$sort = sprintf('%s %s',
				ltrim($sort, '-'),
				(strpos($sort, '-') === 0 ? 'DESC' : 'ASC')
			);
		}
		return $sort;
	}
	
	protected function _limit($limit, $page) {
		if (!$limit) {
			return '';
		}
		$page = max(0, $page);
		return sprintf(' LIMIT %d,%d', (($page-1) * $limit), $limit);
	}
	
	
	protected function _escape_field_value($field_name, $value) {
		// TODO
		return "'" . DB::escape($value) . "'";
	}
	
}



?>