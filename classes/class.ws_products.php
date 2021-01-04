<?php

/**
 * Htt-Aasasoft Webservice Module Conector
 *
 */

/**
 * Product class for conection aasasoft webservice focus on operation products
 *
 * This class contains crud operations for products.
 */
class aasasoft_products extends aasasoft_service_dispatch
{	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**	
	 * [Get Products] Get listing products
	 * 
	 * @param  string uri endpoint service
	 * @return [array htt object]
	 */
	public function get_products_service($params_request="", $uri = "ProdDetalle.aspx")
	{
        $query = http_build_query($params_request);
        $query = preg_replace('/%5B[0-9]+%5D/simU', '', $query);
        $uri .= '?' . $query;
		$request = new aasasoft_request_factory(true, true, "get");
		$request->set_custom_uri($uri);

		$this->debugPrintRequest($request->get_post_message(), __METHOD__);

		$this->webModel()->{__FUNCTION__}($request->get_post_message())->prepareService()->callService();

		$response = $this->getResponse()->toHttObject();

		$this->debugPrintResponse($response, __METHOD__);

		return $response;
	}

	/**
	 * [Get prices] Get listing prices from products
	 * 
	 * @param  string uri endpont service
	 * @return [array htt object]
	 */
	public function get_stock_prices_service($params_request="", $uri = "ProdPreciosExisten.aspx")
	{
        $query = http_build_query($params_request);
        $query = preg_replace('/%5B[0-9]+%5D/simU', '', $query);
        $uri .= '?' . $query;
		$request = new aasasoft_request_factory(true, true, "get");
		$request->set_custom_uri($uri);

		$this->debugPrintRequest($request->get_post_message(), __METHOD__);

		$this->webModel()->{__FUNCTION__}($request->get_post_message())->prepareService()->callService();

		$response = $this->getResponse()->toHttObject();

		$this->debugPrintResponse($response, __METHOD__);

		return $response;
	}

	/**
	 * Obtiene listado de existencias
	 * 
	 * @param  string uri endpont service
	 * @return [array htt object]
	 */
	public function get_all_stock_service($params_request="", $uri = "ProdListadoArticuloExistencia.aspx")
	{
        $query = http_build_query($params_request);
        $query = preg_replace('/%5B[0-9]+%5D/simU', '', $query);
        $uri .= '?' . $query;
		$request = new aasasoft_request_factory(true, true, "get");
		$request->set_custom_uri($uri);

		$this->debugPrintRequest($request->get_post_message(), __METHOD__);

		$this->webModel()->{__FUNCTION__}($request->get_post_message())->prepareService()->callService();

		$response = $this->getResponse()->toHttObject();

		$this->debugPrintResponse($response, __METHOD__);

		return $response;
	}

	/**
	 * Obtiene listado de precios
	 * 
	 * @param  string uri endpont service
	 * @return [array htt object]
	 */
	public function get_all_prices_service($params_request="", $uri = "ProdListadoArticuloPrecio.aspx")
	{
        $query = http_build_query($params_request);
        $query = preg_replace('/%5B[0-9]+%5D/simU', '', $query);
        $uri .= '?' . $query;
		$request = new aasasoft_request_factory(true, true, "get");
		$request->set_custom_uri($uri);

		$this->debugPrintRequest($request->get_post_message(), __METHOD__);

		$this->webModel()->{__FUNCTION__}($request->get_post_message())->prepareService()->callService();

		$response = $this->getResponse()->toHttObject();

		$this->debugPrintResponse($response, __METHOD__);

		return $response;
	}
}