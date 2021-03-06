<?php



class DbConnection {
	
	static protected $_instances = array();
	
	protected $_name = null;
	
	protected $_host     = null;
	protected $_user     = null;
	protected $_pass     = null;
	protected $_database = null;
	protected $_prefix   = '';
	protected $_charset  = null;
	protected $_debug    = false;
	
	protected $_connection = null;
	
	
	static public function add(/* polymorpic */) {
		$args = func_get_args();
		
		if (count($args) === 1) {
			$name   = 'default';
			$config = $args[0];
		} else {
			$name   = $args[0];
			$config = $args[1];
		}
		$self = get_called_class();
		
		return self::$_instances[ $name ] = new $self($name, $config);
	}
	
	static public function get($name) {
		if (!isset(self::$_instances[ $name ])) {
			// TODO, thown an exception
		}
		return self::$_instances[ $name ];
	}
	
	
	public function __construct($name, $config) {
		$this->_name = $name;
		
		foreach ($config as $key=>$value) {
			if (property_exists($this, '_' . $key)) {
				$this->{'_' . $key} = $value;
			}
		}
	}
	
	
	public function prefix() {
		return $this->_prefix;
	}
	
	public function connect() {
		if (!($this->_connection = mysqli_connect($this->_host, $this->_user, $this->_pass))) {
			return false;
		}
		if (!mysqli_select_db($this->_connection, $this->_database)) {
			$this->_connection = null;
			return false;
		}
		if (!empty($this->_charset) && !mysqli_set_charset($this->_connection, $this->_charset)) {
			$this->_connection = null;
			return false;
		}
		
		return true;
	}
	
	public function escape($value) {
		if (!$this->_connection) {
			$this->connect();
		}
		
		return mysqli_real_escape_string($this->_connection, $value);
	}
	
	public function query($query) {
		if (stripos($query, 'SET NAMES') !== 0 && !$this->_connection) {
			$this->connect();
		}
		
		if ($this->_debug) {
			$this->_debug($query);
			$ret = mysqli_query($this->_connection, $query);
			if (!$ret) {
				$this->_debug(mysqli_error($this->_connection), 'darkred');
				exit;
			}
		} else {
			$ret = mysqli_query($this->_connection, $query);
		}
		
		if (!$ret) {
			return false;
		} elseif ($ret === true) {
			if (stripos($query, ' ON DUPLICATE ')!==false) {
				return (boolean) mysqli_affected_rows($this->_connection);
			} elseif (stripos($query, ' INTO ')!== false) {
				return mysqli_insert_id($this->_connection);
			} else {
				//return mysql_affected_rows($this->_connection);
				return true;
			}
		} else {
			$rows = array();
			while($row = mysqli_fetch_assoc($ret)) {
				$rows[] = $row;
			}
			return $rows;
		}
	}
	
	
	protected  function _debug($query, $background = 'darkblue') {
		$query = (string) $query;
		printf('<pre style="%s">%s</pre>',
				'margin:10px 0; padding:5px; border:1px solid black; background:'.
					$background .
					'; color:white; font-size:10px;',
				$query
			);
	}
	
}



?>