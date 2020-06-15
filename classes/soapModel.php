<?php
class soapModel
{
	protected $client;
	
	protected $response;
	
	protected $function_name;
	
	protected $services = array();
	
	protected $url_service = null;
	
	protected $call_options = array();
	
	protected $options = array();
	
	protected $arguments = array();
	
	protected $input_headers = null;
	
	protected $output_headers = array();
	
	protected $action = "";
	
	protected $new_client = false;
	
	protected $mode_debug = false;
	
	protected $connection_previous = null;

	protected $class_logger;

	public function __construct()
	{
		if (defined('SOAP_LOGS_WEBSERVICE_ENABLED')) {
			$this->mode_debug = filter_var(SOAP_LOGS_WEBSERVICE_ENABLED, FILTER_VALIDATE_BOOLEAN);
		}

		$this->init_soap_logger();
	}

	protected function init_soap_logger($require_instance = false)
	{
		if ($this->mode_debug || $require_instance) {
			$this->class_logger = new default_logger($this->mode_debug, default_logger::LOGGER_PRINT_R);
		}
	}

	protected function get_soap_logger()
	{
		if (is_null($this->class_logger)) {
			$this->init_soap_logger(true);
		}

		return $this->class_logger;
	}

	public function __call($method, $args)
	{
		if (!array_key_exists($method, $this->services)) {
			throw new Exception("El servicio no se ha inicializado.");
		}

		$this->function_name = $method;

		$params = $args[0];

		if (is_array($params)) {
			foreach ($params as $param => $value) {
				if (property_exists($this, $param)) {
					$this->$param = $params[$param];
				}
			}
		}

		return $this;
	}

	public function __sleep()
	{
		
		return array ('response','function_name','services','url_service','call_options','options','arguments','input_headers','output_headers','action','new_client', 'mode_debug');
	}

	public function configureUrlService($url)
	{
		
		$this->url_service = $url;
		
		return $this;
	}

	public function switchEndPoint($url)
	{
		if (empty($url)) {
			return $this;
		}

		$this->connection_previous = $this->url_service;
		
		$this->url_service = $url;

		$this->new_client = true;

		return $this;
	}

	public function setEndPoint($url)
	{
		$this->url_service = $url;

		$this->new_client = true;
	}

	public function restoreEndPoint()
	{

		$this->url_service = $this->connection_previous;
		
		$this->new_client = true;
		
		return $this;
	}

	public function defineServices($services = array())
	{
		$this->services = $services;
	}

	public function createXmlRequest($xml)
	{
		if (!empty($xml)) {
			return new SoapVar($xml, XSD_ANYXML);
		}
	}

	public function getClient($reset_options = true)
	{
		if (!$this->new_client && !is_null($this->client)) {
			return $this;
		}

		if ($reset_options) {
			$this->options = $this->mode_debug ? array('trace' => 1) : array();
		}

		if (!$this->url_service) {
			throw new Exception('Necesita establecer la url de servicio.');
		}

		$this->client = new SoapClient($this->url_service, $this->options);

		return $this;
	}

	public function prepareService() {

		if (!array_key_exists($this->function_name, $this->services)) {
			throw new Exception("Servicio no implementado.");
		}

		$this->action = $this->services[$this->function_name];

		if (!is_string($this->action)) {
			throw new Exception("No es valida la acción a invocar.");
		}

		return $this;
	}

	public function resetProperties()
	{
		$this->action = "";
		
		$this->arguments = array();
		
		$this->call_options = array();
		
		$this->input_headers = null;
		
		$this->output_headers = array();
	}

	public function callService()
	{
		try {

			$this->getClient()->execute();

		} catch(Exception $e) {
			$this->resetProperties();
			$this->response = $e->getMessage();
			$this->debuggerException($e);
		}

		return $this;
	}

	public function execute()
	{

		$this->response = $this->client->__soapCall($this->action, $this->arguments, $this->call_options, $this->input_headers, $this->output_headers);

		$this->debuggerRequest()->debuggerResponse();

		$this->resetProperties();

		return $this;
	}

	public function setLocation($location)
	{
		
		$this->client->__setLocation($location);
		
		return $this;
	}

	public function getResponse()
	{

		return $this->response;
	}

	protected function debuggerException($e)
	{
		
		if ($this->mode_debug && $e) {

			$logger = $this->get_soap_logger();

			$logger->save_log('------  exception -----------: ' . $logger->print($e->getMessage()), $logger::IS_ERROR_LOG);
		}
		
		return $this;
	}

	protected function debuggerRequest($add_headers = false) {
		
		if ($this->mode_debug) {

			$logger = $this->get_soap_logger();

			$logger->save_log('------  action_value : '.$this->action.' ------ path : ' . $this->url_service. ' ------ ' . $logger->print($this->client->__getLastRequest()).' ', $logger::IS_REQUEST_LOG);
		}

		return $this;
	}

	protected function debuggerResponse($add_headers = false) {
		
		if ($this->mode_debug) {

			$logger = $this->get_soap_logger();

			$logger->save_log('------  action_value : '.$this->action.' ------ path : ' . $this->url_service. ' ------ ' . $logger->print($this->client->__getLastResponse()).' ', $logger::IS_RESPONSE_LOG);
		}

		return $this;
	}
}
?>
