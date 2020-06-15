<?php
class jsonModel
{
	public $type = 'post';
	
	public $url_service = null;
	
	public $params = array();
	
	public $headers = array();
	
	public $async = false;
	
	public $result = null;
	
	private $info = null;
	
	public $services = array();
	
	public $requestObject;

	public $endpoint_service = null;

	private static $curl_timeout_asynchronous;
	
	private static $curl_timeout_synchronous;
	
	private static $content_type_default = 'Content-Type: application/json';

	public function __construct()
	{
		$this->setSettingsConection()->initSettingRequest();
	}

	private function initSettingRequest()
	{
		$this->requestObject = new stdClass;
		$this->requestObject->custom_uri = null;
		$this->requestObject->action = null;
		$this->requestObject->post_params = null;
		$this->requestObject->async = false;
		$this->requestObject->type = $this->type;
		$this->requestObject->headers = array(self::$content_type_default);
		$this->requestObject->fn_encode = function($params) { return urldecode(http_build_query($params)); };
	}

	public function setSettingsConection()
	{
		self::$curl_timeout_asynchronous = defined('WS_CURL_SETTING_TIMEOUT_ASYNC') ? intval(WS_CURL_SETTING_TIMEOUT_ASYNC) : 1;
		self::$curl_timeout_synchronous = defined('WS_CURL_SETTING_TIMEOUT_SYNC') ? intval(WS_CURL_SETTING_TIMEOUT_SYNC) : 60;

		return $this;
	}

	public function __call($method, $args)
	{
		if (!array_key_exists($method, $this->services)) {
			throw new Exception("El servicio no se ha inicializado.");
		}

		$this->requestObject->action = $method;

		$params = $args[0];

		if (is_array($params)) {
			$this->mapArrayToSettingRequest($params);
		}

		if ($params instanceof stdClass) {
			$this->mapObjectToSettingRequest($params);
		}

		return $this;
	}

	private function mapArrayToSettingRequest($params)
	{
		foreach ($params as $param => $value) {
			if (property_exists($this->requestObject, $param)) {
				$this->requestObject->$param = $params[$param];
			}
		}
	}

	public function defineServices($services = array())
	{
		$this->services = $services;
	}

	public function getParameters()
	{
		return get_object_vars($this);
	}

	public function configureUrlService($url)
	{
		$this->url_service = $url;
		return $this;
	}

	public function getObjectRequest()
	{
		return $this->requestObject;
	}

	private function mapObjectToSettingRequest(stdClass $object_request) {
		
		foreach ($object_request as $key => $value) {
    		$this->requestObject->{$key} = $value;
		}

		return $this;
	}

	public function prepareService() {

		if (empty($this->requestObject->action)) {
			throw new Exception("Debe establecer una acción");
		}

		$this->action = $this->requestObject->action;
		$this->params = is_null($this->requestObject->post_params) ? '' : $this->requestObject->post_params;
		$this->type = isset($this->requestObject->type) ? $this->requestObject->type : "post";
		$this->async = isset($this->requestObject->async) ? $this->requestObject->async  : false;
		$this->headers = isset($this->requestObject->headers) ? $this->requestObject->headers : array();

		return $this;
	}

	public function callService()
	{
		try {
			
			$this->prepareRequest()->execute();

		} catch(Exception $e) {
			$this->response = $e->getMessage();
		}
	}

	private function prepareRequest()
	{
		if (is_null($this->action)) {
			throw new Exception("Debe establecer una acción");
		}

		if (array_key_exists($this->action, $this->services) == false) {
			throw new Exception("Servicio no implementado ");
		}

		if (empty($this->services[$this->action])) {
			throw new Exception("La URI del servicio es requerido");
		}

		if (empty($this->headers)) {
			$this->headers = array(self::$content_type_default);
		}

		$service_action = $this->services[$this->action];

		if (!is_null($this->requestObject->custom_uri)) {
			$service_action = $this->requestObject->custom_uri;
		}

		$this->endpoint_service = $this->url_service . $service_action;

		return $this;
	}

	public function getResponse()
	{
		if (!is_null($this->result)) {
			$this->response = $this->result;
		}

		return $this->response;
	}

	public function getInfo()
	{

		return $this->info;
	}

	private function execute()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->endpoint_service);

		if ($this->type == 'post') {

			if (is_array($this->params)) {

				$params_encode = ($this->requestObject->fn_encode)($this->params);

				curl_setopt($ch,CURLOPT_POST, count($this->params));
				curl_setopt($ch,CURLOPT_POSTFIELDS, $params_encode);
			}
			else{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
			}
		}

		if (is_array($this->headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);  // Cabeceras API
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if ($this->async) {
			curl_setopt($ch, CURLOPT_TIMEOUT, self::$curl_timeout_asynchronous);
		}
		else {
			curl_setopt($ch, CURLOPT_TIMEOUT, self::$curl_timeout_synchronous);
		}
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		
        $this->result = curl_exec($ch);
        // por alguna razon el json viene con salto de linea y causa error, se eliminan los saltos de linea
        $this->result = str_replace(PHP_EOL, '', $this->result);

		$this->info = curl_getinfo($ch);
		
		curl_close($ch);
	}
	
}
?>
