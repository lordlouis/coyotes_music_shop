<?php
class default_logger extends ws_logger implements iLogger
{
	const LOGGER_VAR_EXPORT = 1;

	const LOGGER_PRINT_R = 2;

	const LOGGER_VAR_DUMP = 3;

	private $method_logger;

	/**
	 * [__construct constructor]
	 * @param boolean $enable [enable save logs generated]
	 */
	public function __construct($enable = false, $logger_type = 0)
	{
		parent::__construct();
		
		$this->enable_logger = $enable;

		if ($this->is_valid_logger_method($logger_type)) {
			
			$this->set_method_logger($logger_type);
		}
		else{
			$this->set_default_logger();
		}
	}

	/**
	 * [set_default_logger set default var export as logger main]
	 */
	protected function set_default_logger()
	{
		$this->method_logger = self::LOGGER_VAR_EXPORT;

		if (defined('WS_LOGGER_DEFAULT_METHOD_TO_PRINT') && !empty(WS_LOGGER_DEFAULT_METHOD_TO_PRINT)) {
			
			$this->set_method_logger(WS_LOGGER_DEFAULT_METHOD_TO_PRINT);
		}
	}

	/**
	 * [set_method_logger set logger since outside instance]
	 * @param integer $type_method [type of method printer]
	 */
	public function set_method_logger($type_method = 1)
	{
		$this->method_logger = $type_method;

		if (!$this->is_valid_logger_method($this->method_logger)) {
			
			$this->method_logger = self::LOGGER_VAR_EXPORT;
		}
	}

	protected function is_valid_logger_method($type_method)
	{

		return in_array($type_method, [self::LOGGER_VAR_EXPORT, self::LOGGER_PRINT_R, self::LOGGER_VAR_DUMP]);
	}
	
	/**
	 * [print return content as string]
	 * @param  [object|array] $data [data to print in file]
	 * @param  array  $options [future extends]
	 * @return [string]          [string]
	 */
	public function print($data, $options = [])
	{
		$stringer_data = '';

		if (!$this->enable_logger) {
			
			return $stringer_data;
		}

		switch ($this->method_logger)
		{
			case self::LOGGER_PRINT_R:
				$stringer_data = print_r($data ,true);
				break;
			case self::LOGGER_VAR_DUMP:
				$stringer_data = $this->logger_var_dump($data);
				break;
			case self::LOGGER_VAR_EXPORT:
			default:
				$stringer_data = var_export($data, true);
				break;
		}

		return $stringer_data;
	}

	private function logger_var_dump($data)
	{
		ob_start();
		
		var_dump($data);
		
		$result = ob_get_clean();

		return $result;
	}

}
?>