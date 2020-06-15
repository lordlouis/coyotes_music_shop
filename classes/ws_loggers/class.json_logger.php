<?php
class json_logger extends ws_logger implements iLogger
{
	public function __construct($enable = false)
	{
		parent::__construct();

		$this->enable_logger = $enable;
	}

	public function print($data, $options = [])
	{
		if (!$this->enable_logger) {
			return '';
		}

		return json_encode($data, JSON_PRETTY_PRINT);
	}
}
?>