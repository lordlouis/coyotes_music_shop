<?php
abstract class webConnector
{
	public $instance = null;
	public $model_connector = "";
	public $webConnector = null;

	const jsonModel = "jsonModel";
   	const soapModel = "soapModel";
   	const dbSqlModel = "dbSqlModel";
   	const dbMysqlModel = "dbMysqlModel";

	function __construct(){}

   	public function setWebConnector() {

   		if ($this->model_connector == "") {
   			throw new Exception('Error: debe establecer un modelo de dato.');
   		}

		$this->setWebModel();

		return $this;
	}

	public function defineServices() {
		$this->webModel()->defineServices($this->services);
	}

	public function setWebModel($change = false) {

		if ($this->webConnector == null || $change) {
			// $this->webConnector = $_SESSION['injectorModel']->get($this->model_connector);
			$injectorModel = new injectorModel;
			$this->webConnector = $injectorModel->get($this->model_connector);
		}

		$this->webModel()->defineServices($this->services);
		
		return $this;
	}

   	public function setConnectorJson() {
		$this->model_connector = self::jsonModel;
		return $this;
	}

	public function setConnectorSoap() {
		$this->model_connector = self::soapModel;	
		return $this;
	}

	public function setConnectorSql() {
		$this->model_connector = self::dbMysqlModel;	
		return $this;	
	}
}
?>