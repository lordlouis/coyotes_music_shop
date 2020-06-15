<?php

/**
 * Htt-Aasasoft Webservice Module Conector
 *
 */

require 'classes/ws_loggers/iLogger.php';
require 'classes/ws_loggers/class.ws_logger.php';
require 'classes/ws_loggers/class.json_logger.php';
require 'classes/ws_loggers/class.default_logger.php';
require 'classes/ws_response_mapper_htt/class.ws_response_mapper.php';
require 'classes/soapModel.php';
require 'classes/jsonModel.php';
require 'classes/class.injectorModel.php';
require 'classes/class.webConnector.php';
require 'classes/class.httWebModel.php';

/**
 * Main dispatch class for webservice that makes connection to main endpoint
 *
 * This class make and expose connection for webservice methods.
 */

class aasasoft_service_dispatch extends httWebModel
{
	protected $services = array();
	protected $url_service;
	private static $_service = "/_service$/";
	private static $time_debugger_file = "Y-m-d H:i";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->initializeDataConfig();

		$this->createServices(self::$_service)->setConnectorJson()->setWebConnector();

		$this->webModel()->configureUrlService($this->get_connection_url_ws());
	}

	/**
	 * [SetMapResponse]
	 * Implement abstract method
	 * 
	 * Parser response and add extra information to response from service
	 */
	public function _setMapResponse()
	{
		//add extra info to response;
		if (!is_array($this->mapResponse) || is_null($this->mapResponse)) {
			$this->mapResponse = array("result" => '');
		}

		$this->response = $this->mapResponse['result'] = json_decode($this->response, true);
	}

	/**	
	 * [toHttObject parse to htt object]
	 * @param  boolean $callback [function to invoke]
	 * @return [type]            [htt object]
	 */
	public function toHttObject($callback = false) {
		
		$this->_processResponse(null, false);

		if ($callback) {
			return $this;
		}

		return $this->objectHtt;
	}

	/**
	 * [initializeDataConfig]
	 *
	 * Set url endpoint service main for conection
	 * 
	 * @return void;
	 */
	protected function initializeDataConfig()
	{
		// $url_service = defined('WS_AASASOFT_MODE_SANDBOX') && WS_AASASOFT_MODE_SANDBOX !== 'true' ? WS_AASASOFT_URL_ENDPOINT_LIVE : WS_AASASOFT_URL_ENDPOINT_SANDBOX;
		$url_service = 'https://www.grupogonher.mx/apis/';

		$this->set_connection_url_ws($url_service);
	}

	/**	
	 * [get_connection_url_ws description]
	 * @return [type] [url service]
	 */
	protected function get_connection_url_ws()
	{
		return $this->url_service;
	}

	/**
	 * [set_connection_url_ws description]
	 * @param [string] $endpoint_url [endpoint ws url connection]
	 */
	protected function set_connection_url_ws($endpoint_url)
	{
		if (is_null($endpoint_url)) {
			throw new Exception("La url de conexion está indefinida o no está establecida.", 1);
		}

		$this->url_service = $endpoint_url;
	}

	/**
	 * [debugPrintRequest Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	protected  function debugPrintRequest($request, $name = 'REQUEST_METHOD')
	{
		self::logger('------  service_name : ['.$name.'] ------ ' . var_export($request,true).' ',2, $name);
	}

	/**
	 * [debugPrintResponse Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	protected  function debugPrintResponse($response, $name = 'RESPONSE_METHOD')
	{
		self::logger('------  service_name : ['.$name.'] ------ ' . var_export($response,true).' ',3, $name);
	}

	/**
	 * [debugPrintException Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	public function debugPrintException($message)
	{
		self::logger('------  service_name Exception ------ ' . var_export($message,true).' ',1);
	}

	/**
	 * [debugPrintMessage Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	public function debugPrintMessage($message, $name = 'CLASS_INFO')
	{
		self::logger('------  service_name Info ------ ' . var_export($message,true).' ', 0);	
	}

	/**
	 * [logger helper main method for printer debugger]
	 * @param  [type]  $string_log [object o data in string to debug]
	 * @param  integer $type       [type of log]
	 * @param  string  $name_type  [name file]
	 * @return [void]              [none]
	 */
	public static function logger($string_log, $type = 1, $name_type = '') {
		$name_type = str_replace('::','_',$name_type);
		if (defined('AASASOFT_LOGS_WEBSERVICE_ENABLED') && AASASOFT_LOGS_WEBSERVICE_ENABLED == 'true') {
			$s = 'INFO';
			switch ($type) {
				case 1:
					$s = 'ERROR';
				break;
				case 2:
					$s = 'REQUEST';
				break;
				case 3:
					$s = 'RESPONSE';
				break;
				case 4:
					$s = 'SUCCESS';
				break;
				case 5:
					$s = 'FATAL ERROR';
			}
			$arch = fopen(DIR_FS_CATALOG . "/logs/debug-".(empty($name_type) ? $s : $name_type).'_'.str_replace(' ','_',date('d-m-Y H:i')), "a+");
			fwrite($arch, "[".date(self::$time_debugger_file)." "." - $s ] ".$string_log.' page:'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ."\n");
			fclose($arch);
		}
	}
}
