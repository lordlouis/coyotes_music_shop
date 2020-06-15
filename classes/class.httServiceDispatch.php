<?php
class httServiceDispatch extends httWebModel
{	
	/*
	Example How to initiali a service model for JSON webservice
   /*public $services = [];

   public $url_service = "https://jsonplaceholder.typicode.com";

   private $ws_logger;*/

	/**
	 * [__construct]*/
	 
	/*public function __construct()
	{
		parent::__construct();

		/*$this->createServices()->setConnectorJson()->setWebConnector();

		$this->webModel()->configureUrlService($this->url_service);

		$this->ws_logger = $this->set_enable_logger(true)->get_logger();
	}*/
	

	/**
	 * [_setMapResponse Required implement abstract method ]
	 * Works like decorator for the response
	 *
	public function _setMapResponse()
	{
		//add extra info to response;
		if (!is_array($this->mapResponse) || is_null($this->mapResponse)) {
			$this->mapResponse = array("result" => '');
		}

		$this->response = $this->mapResponse['result'] = json_decode($this->response, true);
	}*/
	

	/**
	 * Example How to call a service model 
	 * 
	 *
	 * [getAllPostService must be postfixe name  _Service for identify each service]
	 * @param  string $uri [works like annotacion for build method -> uri_service ]
	 * @return [type]      [response htt object or like defined in _setMapResponse]
	 
	public function getAllPostService($uri = "/posts") {

		$request = new stdClass;

		$request->type = "get"; // default post
		
		$uri = "/posts?userId=";

		$request->headers = array("Content_type: application/json");

		$request->custom_uri = $uri . rand(1,10);

		 Or declare request as array ...
		 
		 $request = array("headers" => array("Content_type: application/json"), 
						  "type" => "get",
						  "custom_uri" => $uri .rand(1,10));
		
		$this->debugPrintRequest($request->get_post_message(), __METHOD__);

		$this->webModel()->{__FUNCTION__}($request)->prepareService()->callService();

		$response = $this->getResponse()->toHttObject();

		$this->ws_logger->debugPrintResponse($response, __METHOD__);
	}*/

	/*
	 My another service with pattern name _Service ...
	
	public function getCommentsService($uri = "/posts/1/comments")
	{
		
	}
	*/

}
?>