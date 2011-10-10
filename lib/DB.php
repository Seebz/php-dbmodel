<?php



class DB {
	
	private static $_host     = null;
	private static $_user     = null;
	private static $_pass     = null;
	private static $_database = null;
	private static $_prefix   = '';
	private static $_charset  = null;
	private static $_debug    = false;
	
	private static $_connection = null;
	
	
	public static function Construct($config) {
		foreach ($config as $key=>$value) {
			if (property_exists(__CLASS__, '_' . $key)) {
				self::${'_' . $key} = $value;
			}
		}
	}
	
	public static function getPrefix() {
		return self::$_prefix;
	}
	
	public static function connect() {
		if (!(self::$_connection = mysql_connect(self::$_host, self::$_user, self::$_pass))) {
			return false;
		}
		if (!mysql_select_db(self::$_database, self::$_connection)) {
			self::$_connection = null;
			return false;
		}
		if (!empty(self::$_charset) && !mysql_set_charset(self::$_charset, self::$_connection)) {
			self::$_connection = null;
			return false;
		}
		
		return true;
	}
	
	public static function escape($value) {
		if (!self::$_connection) {
			self::connect();
		}
		
		return (self::$_connection ? mysql_real_escape_string($value, self::$_connection) : mysql_escape_string($value));
	}
	
	public static function query($query) {
		if (stripos($query, 'SET NAMES') !== 0 && !self::$_connection) {
			self::connect();
		}
		
		if (static::$_debug) {
			self::_debug($query);
			$ret = mysql_query($query);
			if (!$ret) {
				self::_debug(mysql_error(), 'darkred');
				exit;
			}
		} else {
			$ret = mysql_query($query);
		}
		
		if (!$ret) {
			return false;
		} elseif ($ret === true) {
			if (stripos($query, ' ON DUPLICATE ')!==false) {
				return (boolean) mysql_affected_rows(self::$_connection);
			} elseif (stripos($query, ' INTO ')!== false) {
				return mysql_insert_id(self::$_connection);
			} else {
				//return mysql_affected_rows(self::$_connection);
				return true;
			}
		} else {
			$rows = array();
			while($row = mysql_fetch_assoc($ret)) {
				$rows[] = $row;
			}
			return $rows;
		}
	}
	
	
	protected static function _debug($query, $background = 'darkblue') {
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