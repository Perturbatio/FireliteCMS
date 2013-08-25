<?php namespace Firelite\Models;
use \Eloquent;
use \Str;
use \Validator;

class Model extends Eloquent {
	protected $_messages = array();
	public $validation = array(
		'rules' => array(),
		'messages' => array()
	);
	
	/**
	 * magic get to return derived values if the method getPropertyName exists (camelcase)
	 * 
	 * @param type $property
	 * @return type 
	 */
	public function __get($property){
		$get_method =  Str::camelCase('get_' .$property);
		
		if (method_exists($this, $get_method)){
			return $this->$get_method();
		} else {
			return parent::__get($property);
		}
	}
	
	/**
	 * Empties the relationship data to force a reload of only this relationship
	 * 
	 * @param string $relationship 
	 */
	public function clear_relationship($relationship){
		unset( $this->relationships[$relationship] );
		
	}
	
	/**
	 * Reload the model from the database 
	 */
	public function reload(){
		//TODO: find a better way to do this
		$attrs = \DB::table(static::table())
				->where(static::$key, '=', $this->id)
				->get();
		
		if (!empty($attrs)){
			$attrs = get_object_vars( $attrs[0] );
			$this->attributes = $attrs;
			$this->original = $attrs;
			return true;
		}
		return false;
	}
	
	/**
	 * Throw an error
	 * @param String $msg
	 * @param String $key = "" (Optional)
	 * @return Boolean
	 */
	protected function throwError($msg, $key = null){
		return $this->add_message($msg, FIRELITE_MESSAGETYPE_ERROR, $key);
	}
	
	/**
	 * Clear the errors for this class
	 * 
	 * @return Boolean
	 */
	public function clearErrors(){
		return $this->clearMessages(FIRELITE_MESSAGETYPE_ERROR);
	}
	
	/**
	 * Return the errors for this class
	 * 
	 * @param Boolean $autoClear = false  (Optional)
	 * @return String[]
	 */
	public function getErrors( $autoClear = false ){
		return $this->get_messages(FIRELITE_MESSAGETYPE_ERROR, true, $autoClear);
	}
	
	/**
	 * 
	 * @param $error_key
	 * @return String/False
	 */
	public function getError($error_key){
		return $this->get_message( $error_key, FIRELITE_MESSAGETYPE_ERROR );
	}
	
	/**
	 * 
	 * @return Integer
	 */
	public function countErrors(){
		return $this->countMessages(FIRELITE_MESSAGETYPE_ERROR);
	}
	
	/**
	 * 
	 * @return Boolean
	 */
	public function hasErrors(){
		return $this->has_messages(FIRELITE_MESSAGETYPE_ERROR);
	}
	
	/**
	 * 
	 * @param $message
	 * @param $message_type
	 * @param $key
	 * @return Boolean
	 */
	protected function add_message( $message, $message_type = FIRELITE_MESSAGETYPE_MESSAGE,  $key = null ){
		$msg = new \FireliteMessage($message, $message_type);
		
		if ($key !== null){
			$this->_messages[$key] = $msg;
		} else {
			$this->_messages[] = $msg;
		}
		return true;
	}
	
	/**
	 * 
	 * @param $message_type
	 * @return Boolean
	 */
	public function has_messages( $message_type = FIRELITE_MESSAGETYPE_ANY ){
		return $this->countMessages($message_type) > 0;
	}
	
	/**
	 * 
	 * @param $message_type
	 * @return Integer
	 */
	public function countMessages($message_type = FIRELITE_MESSAGETYPE_ANY){
		$count = 0;
		
		switch($message_type){
			case FIRELITE_MESSAGETYPE_ANY:
				$count = count($this->_messages);
			break;
			case FIRELITE_MESSAGETYPE_ERROR://cascade to default
			case FIRELITE_MESSAGETYPE_MESSAGE://cascade to default
			default:
				foreach($this->_messages as $message){
					if ($message->type == $message_type){
						$count++;
					}
				}
			break;
		}
		
		return $count;
	}
	
	/**
	 * 
	 * @param $message_key
	 * @param $message_type
	 * @return String/False
	 */
	public function get_message($message_key, $message_type = FIRELITE_MESSAGETYPE_ANY, $as_string = true){
		switch($message_type){
			case FIRELITE_MESSAGETYPE_ANY:
				if ( isset( $this->_messages[ $message_key ] ) ){
					return $this->_messages[ $message_key ];
				}
			break;
			case FIRELITE_MESSAGETYPE_MESSAGE:
			case FIRELITE_MESSAGETYPE_ERROR:
			default:
				if ( isset( $this->_messages[ $message_key ] )  ){
					$message = $this->_messages[ $message_key ];
					if ($message->type == $message_type){
						if ( $as_string ){
							return $message->text;
						} else {
							return $message;
						}
					}
				}
			break;
		}
		return false;
	}
	
	/**
	 * 
	 * @param $message_type
	 * @param $as_string
	 * @return String[]
	 */
	public function get_messages($message_type = FIRELITE_MESSAGETYPE_ANY, $as_string = true, $autoClear = false){
		$result = Array();
		foreach( $this->_messages as $key => $value ){
			if ($message_type != FIRELITE_MESSAGETYPE_ANY && $value->type != $message_type){
				continue;
			}
			if ($as_string){
				$result[$key] = $value->text;
			} else {
				$result[$key] = $value;
			}
		}
		if ($autoClear){
			$this->clearMessages($message_type);
		}
		return $result;
	}
	
	/**
	 * Clear error messages from the model
	 * 
	 * The optional message_type param can be used to clear only these types of messages possible values are: <br>
	 * FIRELITE_MESSAGETYPE_ANY<br> 
	 * FIRELITE_MESSAGETYPE_ERROR<br> 
	 * FIRELITE_MESSAGETYPE_MESSAGE
	 * 
	 * @param FIRELITE_MESSAGETYPE_XXX $message_type (optional) clear only the specified message type
	 * @return boolean
	 */
	public function clearMessages($message_type = FIRELITE_MESSAGETYPE_ANY){
		if ( $message_type == FIRELITE_MESSAGETYPE_ANY ){
			$this->_messages = array();
			return true;
		}
		$temp = array();
		foreach($this->_messages as $key=>$message){
			if ( $message->type != $message_type ){
				$temp[$key] = $message;
			}
		}
		$this->_messages = $temp;
		return true;
	}
	
	
	/**
	 * validate the model using the default rules
	 * rules and messages overrides can be specified
	 * use an empy value in the override rule to remove the rule from validation
	 * 
	 * @param array $input An array of input data to validate
	 * @param array $rules_override (optional) specify rules to overwrite, use empty values to remove the rule
	 * @param array $messages_override (optional) specify message to overwrite, use empty values to remove the message
	 * @return Validator|true
	 */
	public function validate($input, $rules_override = array(), $messages_override = array()){
		$rules_override = (array)$rules_override;
		$messages_override = (array)$messages_override;
		
		$rules = $this->validation['rules'];
		$messages = $this->validation['messages'];
				
		foreach( $rules_override as $rule_name => $value ){
			if ( empty($value) ){
				unset( $rules[$rule_name] );
			} else {
				$rules[$rule_name] = $value;
			}
		}
		
		foreach( $messages_override as $rule_name => $value ){
			if ( empty($value) ){
				unset( $messages[$rule_name] );
			} else {
				$messages[$rule_name] = $value;
			}
		}
		
		$validator = Validator::make( $input, $rules, $messages );
		
		if ($validator->valid()){
			return true;
		}
		
		foreach($validator->errors->messages as $key=>$message){
			foreach($message as $value){
				$this->throwError($value, $key);
			}
		}
		return $validator;
	}
}