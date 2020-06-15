<?php

/**
 * Htt-Aasasoft Webservice Module Conector
 *
 */


/**
 * A class factory of layout request for aasasoft webservices.
 *
 * This class factory create layout request with fields defaults.
 */
class aasasoft_request_factory
{
	private $request;
	private static $private_key;
	private static $field_private_key = "privatekey";
	private $token;
	private $callback_type = 'json';

	/**
	 * [__construct init constructor]
	 */
	public function __construct($init_default_request = true, $add_key_private = true, $type = "post", $update_token = true)
	{
		/*
		if ($update_token) {
			$this->token = (new tokenizer())->get_token();
		}
		*/
		
		$this->set_private_key();

		if ($init_default_request) {
			//$this->get_request_post_field_default($add_key_private);
		}
		switch($type){
			case "post":
			$this->get_request_post_field_default($add_key_private);
			break;
			case "get":
			$this->get_request_get_field_default($add_key_private);
			break;
		}
	}

	/**
	 * [set_private_key Set private key for use to request to webservices]
	 */
	protected function set_private_key()
	{
		self::$private_key = defined('WS_AASASOFT_ACCESS_TOKEN') ? WS_AASASOFT_ACCESS_TOKEN : '';
		/*
		self::$private_key = $this->token['access_token'];
		*/
	}

	/**
	 * [init_post_request init message post]
	 * @return [assasoft_dispath] [message post for request]
	*/
	protected function init_post_request()
	{
		$this->request = new stdClass;
		$this->request->type = "post";
		$this->request->headers = array();

		return $this;
	}

	protected function init_get_request()
	{
		$this->request = new stdClass;
		$this->request->type = "get";
		$this->request->headers = array();
		return $this;
	}

	/**
	 * [add_callback_before_request add extra parameters to post message]
	 */
	protected function add_callback_before_request()
	{
		switch($this->callback_type){
			case 'json':
				$this->request->fn_encode = function($params) { return json_encode($params); };
			break;
			case 'http_query':
				$this->request->fn_encode = function($params) { return http_build_query($params); };
			break;

		}

		return $this;
	}

	/**
	 * [get_post_message return message post]
	 * @return [stdClass] [message post for request to ws]
	 */
	public function get_post_message()
	{
		return $this->request;
	}
	
	public function set_custom_uri($uri){
		$this->request->custom_uri = $uri;
	}
	
	public function set_headers($headers){
		$this->request->headers = $headers;
	}
	
	public function set_callback_type($callback_type){
		$this->callback_type = $callback_type;
	}

	/**
	 * [add_parameter_private_key set field private key to post_message]
	 */
	protected function add_parameter_private_key()
	{
		$authorization = self::get_private_key_params();
		$this->request->headers = array_merge($this->request->headers, $authorization);
		$this->request->headers = array_merge($this->request->headers, array('Content-Type: application/json'));

		return $this;
	}

	/**
	 * [get_private_key_params get params mandatory for request]
	 * @return [array] [key value private key request]
	 */
	protected static function get_private_key_params()
	{
		// return array(self::$field_private_key => self::$private_key);
		return array('Authorization: Bearer ' . self::$private_key);
	}

	/**
	 * [add_parameter_to_request description]
	 * @param $post_params [description]
	*/
	public function add_parameter_to_request($post_params)
	{
		if (!is_array($this->request->post_params)) {
			$this->request->post_params = array();
		}

		if (is_array($post_params) && count($post_params)) {
			foreach ($post_params as $key => $value) {
				$this->request->post_params[$key] = $value;
			}
		}
		else{
			$this->request->post_params = $post_params;
		}

		return $this;
	}

	/**
	 * [get_request_post_field_default get self intance for manipulate post message]
	 * @param  boolean $use_key_private [auth for invoke ws]
	 * @return [object aasasoft_dispath]  [self instance]
	*/
	public function get_request_post_field_default($use_key_private = true)
	{
		$this->init_post_request()->add_callback_before_request();
		
		if ($use_key_private) {
			// $this->add_parameter_private_key();
		}

		return $this;
	}
	public function get_request_get_field_default($use_key_private = true)
	{
		$this->init_get_request()->add_callback_before_request();
		
		if ($use_key_private) {
			// $this->add_parameter_private_key();
		}

		return $this;
	}

}
