<?php



/**
 * Classe de validation des modèles
 */
class Validator {
	
	public static $messages = array(
		'presence_of' => '{FIELD_NAME} is required',
		'length_of'   => '{FIELD_NAME}',
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
		$this->_rules      = array_filter((array) $class_name::$validations);
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
	public function validates_presence_of($field_name, $field_value, $message, $args = array()) {
		if (empty($field_value)) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
	}
	
	public function validates_length_of($field_name, $field_value, $message, $args = array()) {
		$args = (array) $args;
		if (isset($args['min']) && strlen($field_value) < $args['min']) {
			return $this->_errors[ $field_name ] = $this->_format_message($field_name, $message, $args);
		}
		if (isset($args['max']) && strlen($field_value) > $args['max']) {
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
			$this->_validate_field_by_rule($rule, $field_name, $field_value, $params);
			
			/*
			$method = 'validates_' . $rule;
			if (method_exists($this, $method)) {
				
				$params  = $this->_format_validation_params($field_name, $rules, $params);
				$message = $params['message'];
				call_user_func_array(array($this, $method),
						compact('field_name', 'field_value', 'message', 'params')
					);
			}
			*/
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
			$params['message'] = static::$messages[ $rule ];
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
		
		// TODO: faire un remplacement des arguments dans le message
		// ex: arg 'min/max' de `length_of`
		
		return $message;
	}
	
}



?>