<?php



/**
 * Classe de validation des modèles
 */
class Validator {
	
	public static $messages = array(
		'presence'  => '{FIELD_NAME} is required',
		'length'    => array(
				'min' => '{FIELD_NAME} is too short (minimum is {MIN})',
				'max' => '{FIELD_NAME} is too short (minimum is {MAX})',
			),
		'inclusion' => '{FIELD_NAME} is not allowed',
		'exclusion' => '{FIELD_NAME} is not allowed',
		'format'    => array(
				'email'  => '{FIELD_NAME} is not a valid email',
				'ip'     => '{FIELD_NAME} is not a valid ip',
				'url'    => '{FIELD_NAME} is not a valid url',
				'regexp' => '{FIELD_NAME} is not valid',
			),
		'uniqueness' => '{FIELD_NAME} must be unique',
		
		'confirmation_of' => "{FIELD_NAME} doesn't match confirmation",
	);
	
		
	protected $_object     = null;
	protected $_class_name = null;
	protected $_rules      = array();
	
	protected $_is_valid   = null;
	protected $_errors     = array();
	
	
	public function __construct(Model $object) {
		$class_name        = get_class($object);
		$this->_object     = $object;
		$this->_class_name = $class_name;
		$this->_rules      = $object->getValidationRules();
	}
	
	
	
	/**
	 * External Methods
	 */
	public function is_valid() {
		$this->_run();
		return ($this->_is_valid === true);
	}
	
	public function is_invalid() {
		return !$this->is_valid();
	}
	
	public function get_errors() {
		return $this->_errors;
	}
	
	
	
	/**
	 * Validation Methods
	 */
	public function validates_presence($field_name, $field_value, $message, $args = array()) {
		if (empty($field_value)) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_length($field_name, $field_value, $message, $args = array()) {
		$args = (array) $args;
		if (isset($args['min']) && strlen($field_value) < $args['min']) {
			$message = (is_array($message) && isset($message['min']) ? $message['min'] : $message);
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
		if (isset($args['max']) && strlen($field_value) > $args['max']) {
			$message = (is_array($message) && isset($message['max']) ? $message['max'] : $message);
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_inclusion($field_name, $field_value, $message, $args = array()) {
		$defaults = array('in' => array(), 'case' => false);
		$args = (array) $args + $defaults;
		
		$in = (array) $args['in'];
		if ($args['case']) {
			// http://php.net/manual/en/function.in-array.php#101976
			$in = array_map(function($x){return (string) $x;}, $in);
			$valid = in_array((string) $field_value, $in, true);
		} else {
			$in = array_map(function($x){return strtolower($x);}, $in);
			$valid = in_array(strtolower($field_value), $in, true);
		}
		if (!$valid) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_exclusion($field_name, $field_value, $message, $args = array()) {
		$defaults = array('in' => array(), 'case' => false);
		$args = (array) $args + $defaults;
		
		$in = (array) $args['in'];
		if ($args['case']) {
			// http://php.net/manual/en/function.in-array.php#101976
			$in = array_map(function($x){return (string) $x;}, $in);
			$valid = !in_array((string) $field_value, $in, true);
		} else {
			$in = array_map(function($x){return strtolower($x);}, $in);
			$valid = !in_array(strtolower($field_value), $in, true);
		}
		if (!$valid) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_format($field_name, $field_value, $message, $args = array()) {
		$defaults = array('type' => null, 'regexp' => null);
		$args = (array) $args + $defaults;
		
		if (!$args['type'] && $args['regexp']) {
			$args['type'] = 'regexp';
		}
		
		$valid = true;
		switch($args['type']) {
			case 'email':
				$valid = filter_var($field_value, FILTER_VALIDATE_EMAIL);
				$message = (is_array($message) && isset($message['email']) ? $message['email'] : $message);
				break;
			case 'ip':
				$valid = filter_var($field_value, FILTER_VALIDATE_IP);
				$message = (is_array($message) && isset($message['ip']) ? $message['ip'] : $message);
				break;
			case 'url':
				$valid = filter_var($field_value, FILTER_VALIDATE_URL);
				$message = (is_array($message) && isset($message['url']) ? $message['url'] : $message);
				break;
			case 'regexp':
				$valid = preg_match($args['regexp'], $field_value);
				$message = (is_array($message) && isset($message['regexp']) ? $message['regexp'] : $message);
				break;
		}
		if (!$valid) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_uniqueness($field_name, $field_value, $message, $args = array()) {
		$object = $this->_object;
		$class_name = $this->_class_name;
		
		$conditions = array(sprintf("%s = '%s'", $field_name, $class_name::connection()->escape($field_value)));
		if ($object->get_pk()) {
			$conditions[] = sprintf("%s <> '%s'", $class_name::primary_key(), $class_name::connection()->escape($object->get_pk()));
		}
		if ($class_name::first(compact('conditions'))) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_confirmation_of($field_name, $field_value, $message, $args = array()) {
		$object = $this->_object;
		
		$c_field = (isset($args['field']) ? $args['field'] : null);
		$c_value = ($c_field && isset($object->{$c_field}) ? $object->{$c_field} : null );
		if ($field_value !== $c_value) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	
	
	/**
	 * Protected Methods
	 */
	protected function _reset() {
		$this->_is_valid = null;
		$this->_errors   = array();
	}
	
	protected function _run() {
		$this->_reset();
		
		foreach($this->_rules as $field_name => $rules) {
			$this->_validate_field($field_name, $rules);
		}
		
		$this->_is_valid = (count($this->_errors) === 0);
	}
	
	protected function _validate_field($field_name, $rules) {
		$field_value = $this->_object->read_attribute( $field_name );
		
		$rules = (array) $rules;
		foreach($rules as $rule => $params) {
			if ( !empty($field_value) || !isset($params['skip_empty']) || !$params['skip_empty'] ) {
				$this->_validate_field_by_rule($rule, $field_name, $field_value, $params);
			}
		}
	}
	
	protected function _validate_field_by_rule($rule, $field_name, $field_value, $params) {
		$method = 'validates_' . $rule;
		if (!method_exists($this, $method)) { return; }
		
		$params = (array) $params;
		if (!isset($params[0]) || !is_array($params[0])) {
			$params = array($params);
		}
		
		foreach($params as $p) {
			if (array_key_exists($field_name, $this->_errors)) {
				return false; // break
			}
		
			$p  = $this->_format_validation_params($field_name, $rule, $p);
			$message = $p['message'];
			call_user_func_array(array($this, $method),
					compact('field_name', 'field_value', 'message', 'p')
				);
		}
	}
	
	protected function _format_validation_params($field_name, $rule, $params) {
		if (isset($params[0])) {
			$params['message'] = $params[0];
			unset($params[0]);
		} elseif (!isset($params['message'])) {
			$params['message'] = (isset(static::$messages[ $rule ]) ? static::$messages[ $rule ] : '');
		}
		
		return $params;
	}
	
	protected function _format_message($field_name, $message, $args) {
		if (strpos($message, '{FIELD_NAME}') !== false) {
			$message = str_replace('{FIELD_NAME}',
					Inflector::humanize($field_name),
					$message
				);
		}
		foreach($args as $key => $value) {
			if (is_string($value) || is_numeric($value)) {
				$message = str_replace(sprintf('{%s}', strtoupper($key)),
						$value,
						$message
					);
			}
		}
		
		return $message;
	}
	
}



?>