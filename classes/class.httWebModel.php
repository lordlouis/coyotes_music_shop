<?php
/**
 * @httWebModel
 *
 * 
 */
abstract class httWebModel extends webConnector
{
	public $objectResponse = null;

	public $httModel = [];
	
	public $webModel = null;
	
	public $settings = null;
	
	protected $model;
	
	public $service = [];
	
	public $config = null;
	
	public $objectHtt = array();
	
	protected $enable_logger = false;

	protected $logger;

	public $response = [];

	public $info = [];

	protected $mapResponse = null;

	protected $ws_logger_class;

	public function __construct()
	{
		if (defined('WS_CLASS_LOGGER_TO_USE') && WS_CLASS_LOGGER_TO_USE !== '') {
			$this->set_ws_class_logger(WS_CLASS_LOGGER_TO_USE);
		}
	}

	/**
	 * [_setMapResponse description]
	 */
	abstract function _setMapResponse();

	/**
	 * [getResponse Return response of webservice]
	 * @return [webConnector] [self class]
	 */
	public function getResponse()
	{
		$this->response = $this->webModel()->getResponse();
		
		return $this;
	}

	public function getInfo()
	{
		$this->info = $this->webModel()->getInfo();
		
		return $this;
	}

	/**
	 * [setSettingConnector helper to use]
	 */
	public function setSettingConnector()
	{
		$this->webModel()->getSetting();
	}

	/**
	 * [webModel return instance of webModel]
	 * @param  [type] $endpoint [link to webservice]
	 * @return [webConnector]  [class that implement webConnector]
	 */
	public function webModel($endpoint = null)
	{
		if (!is_null($endpoint) && !is_null($this->webConnector))
		{
			$this->webConnector->setEndPoint($endpoint);
		}

		return $this->webConnector;
	}

	/**
	 * [execute Apply requesto to endpoint]
	 * @return [webConnector] [self class]
	 */
	public function execute()
	{
		
		$this->webModel()->execute();
		
		return $this;
	}

	/**
	 * [arrayToObject Cast array to object stdClass]
	 * @param  [array] $data [array to cast]
	 * @return [stdClass]    [stdClass class of input array]
	 */
	protected function arrayToObject($data)
	{
		if (is_array($data)) {
			return json_decode(json_encode($data));
		}

		return $data;
	}

	/**
	 * [_processResponse set stdClass to ObjectHtt]
	 * @param  [type]  $callbacks         [not in use]
	 * @param  boolean $convert_to_object [convert to class object]
	 * @return [none]                     
	 */
	protected function _processResponse($callbacks = null, $convert_to_object = true)
	{
		$this->_setMapResponse();

		if ($convert_to_object) {
			$this->response = $this->arrayToObject($this->response);
		}

		$this->objectHtt = $this->response;
	}

	/**
	 * [toHttObject return objectHtt]
	 * @param  boolean $callback [return instance for manipulate response]
	 * @return [class]           [return instance class]
	 */
	public function toHttObject($callback = false)
	{	
		$this->_processResponse();

		if ($callback) {
			return $this;
		}

		return $this->objectHtt;
	}

	/**
	 * 
	 * [createServices Creator of service to webservice method]
	 *
	 * For each method with suffix target_service is add to array services
	 *
	 * Each method point to uri resource declared like parameter optional
	 *
	 * Example: public function new_order_service($params_request, $uri = "api/Order/Post"):
	 *
	 * new_order_service -> call to resource  {endpoint}/api/Order/Post
	 * 
	 * @param  string $target_service [suffix of method, not all methods of a class can be services]
	 * @param  string $param_service  [uri resource to call for this service]
	 * @return [httWebModel]          [instance for require enchaining method]
	 */
	public function createServices($target_service = '/Service$/', $param_service = "uri") {

		if (is_null($target_service)) {
			$target_service = '/Service$/';
		}
		
		$dispatch_service_class = new ReflectionClass($this);
		
		foreach ($dispatch_service_class->getMethods() as $_service_method) {

			if (preg_match($target_service, $_service_method->name)) {

				$reflect_method = new ReflectionMethod($this, $_service_method->name);
				
				$params = $reflect_method->getParameters();

				foreach ($params as $param) {
					
					if ($param->getName() == $param_service && !array_key_exists($_service_method->name, $this->services)) {

						$this->services[$_service_method->name] = $param->getDefaultValue();
						
						break;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * [set_enable_logger enable if is logger is active o not]
	 * @param [boolean] $enable_logger [active o not class debug]
	 */
	protected function set_enable_logger($enable_logger)
	{

		$this->enable_logger = $enable_logger;

		return $this;
	}

	protected function is_valid_class_logger($class_logger, $logger_interface = "iLogger") {

		if (class_exists($class_logger)) {

			$interface_class_logger = class_implements($class_logger);

			return (isset($interface_class_logger[$logger_interface]));
		}

		return false;
	}

	/*
	 * Implement logger class.
	 *
	 * If class not found use default_logger instance
	 * 
	 * 
	 * To implement another class logger extends of ws_logger and implement interface iLogger
	 * 
	*/
	protected function implement_logger()
	{

		if (!empty($this->ws_logger_class) && $this->is_valid_class_logger($this->ws_logger_class)) {

			$this->logger = new $this->ws_logger_class($this->enable_logger);

			return $this->logger;
		}

		$this->use_logger_default();

		return $this->logger;
	}

	/**
	 * [use_logger_default Instancia clase default_logger]
	 * @return [httWebModel] [self class for enchaining]
	 */
	protected function use_logger_default()
	{

		$this->logger = new default_logger($this->enable_logger);

		return $this;
	}

	/**
	 * [set_ws_class_logger implementa logger]
	 * @param [string] $ws_logger_class [name of class to instance]
	 */
	protected function set_ws_class_logger($ws_logger_class)
	{
		$this->ws_logger_class = $ws_logger_class;

		return $this;
	}

	/**
	 * [get_logger return instance of class logger]
	 * @return [ILogger] [class for debug]
	 */
	protected function get_logger()
	{
		return (is_null($this->logger)) ? $this->implement_logger() : $this->logger;
	}

}
?>
