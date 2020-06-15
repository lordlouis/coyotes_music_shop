<?php 
class ws_logger
{	
	protected $time_debugger_file;

	protected $enable_logger = false;

	protected $mode_file_logger = "a+";

	protected $log_type;

	protected $name_log_type;

	const IS_ERROR_LOG = 1;

	const IS_REQUEST_LOG = 2;

	const IS_RESPONSE_LOG = 3;

	const IS_SUCCESS_LOG = 4;

	const IS_FATAL_ERROR_LOG = 5;

	const IS_INFO_LOG = 6;

	const LOG_PATH_END = "/logs/";

	const EXTENSION_END_FILE_LOG = '.log';
	
	protected $type_mode_logger = [];

	protected $path_logger;

	protected $path_to_folder;

	protected $data_log;

	protected $content_file_log;

	protected $path_filename;

	const PREFIX_DEBUG_FILE = 'debug_';

	public function __construct()
	{
		$this->init_settings_log();	
	}

	protected function init_settings_log()
	{
		$this->set_timer_debugger_file("Y-m-d H:i");

		$this->set_types_logs();

		$this->set_path_folder();
	}

	protected function set_path_folder()
	{
		if (defined('FOLDER_PATH_CUSTOM_LOGGER') && FOLDER_PATH_CUSTOM_LOGGER !== '') {
			
			$this->path_to_folder = FOLDER_PATH_CUSTOM_LOGGER;

			return;
		}

		$this->path_to_folder = DIR_FS_CATALOG . self::LOG_PATH_END;
	}

	protected function set_string_log($data_log)
	{
		$this->data_log = $data_log;

		return $this;
	}

	protected function set_name_log_type($name_type, $remove_colon_method = true)
	{
		if ($remove_colon_method) {
			$name_type = str_replace('::','_',$name_type);	
		}

		$this->name_log_type = $name_type;

		return $this;
	}

	protected function set_log_type($log_type)
	{
		$this->log_type = $log_type;

		return $this;
	}

	protected function set_types_logs()
	{
		$this->type_mode_logger = [
			self::IS_ERROR_LOG => 'ERROR',
			self::IS_REQUEST_LOG => 'REQUEST',
			self::IS_RESPONSE_LOG => 'RESPONSE',
			self::IS_SUCCESS_LOG => 'SUCCESS',
			self::IS_FATAL_ERROR_LOG => 'FATAL ERROR',
			self::IS_INFO_LOG => 'INFO'
		];
	}

	protected function get_type_mode_logger($type)
	{
		return isset($this->type_mode_logger[$type]) ? $this->type_mode_logger[$type] : $this->type_mode_logger[self::IS_INFO_LOG];
	}

	protected function set_timer_debugger_file($time_debugger_file)
	{
		$this->time_debugger_file = $time_debugger_file;
	}

	protected function add_prefix_suffix_to_filename($name_file, $suffix = '')
	{
		if (empty($suffix)) {
			$suffix = '_' . str_replace(' ', '_', date('d_m_Y H_i'));
		}
		
		return self::PREFIX_DEBUG_FILE . $name_file . $suffix . self::EXTENSION_END_FILE_LOG;
	}

	protected function add_prefix_suffix_to_data()
	{		
		$type_mode_logger = $this->get_type_mode_logger($this->log_type);

		 return "[" . date('Y-m-d H:i') . " "." - $type_mode_logger ] " . $this->data_log . ' page:' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
	}

	/**
	 * [set_log main method for printer debugger]
	 * @param  [string]  $data_log [object o data in string to debug]
	 * @param  integer $type_log       [type of log]
	 * @param  string  $name_type  [name file]
	 * @return [void]              [none]
	 */
	public function save_log($data_log, $log_type = 1, $name_log_type = '') {
		
		if ($this->enable_logger) {
			
			$this->set_string_log($data_log)->set_log_type($log_type)->set_name_log_type($name_log_type);

			$this->save_to_file();
		}
	}

	protected function prepare_data_file()
	{
		$filename = (empty($this->name_log_type)) ? $this->get_type_mode_logger($this->log_type) : $this->name_log_type;

		$this->path_filename = $this->path_to_folder . $this->add_prefix_suffix_to_filename($filename);

		$this->content_file_log =  $this->add_prefix_suffix_to_data();
	}

	protected function save_to_file()
	{
		$this->prepare_data_file();

		$path_to_file = fopen($this->path_filename, $this->mode_file_logger);

		fwrite($path_to_file, $this->content_file_log);

		fclose($path_to_file);
	}

	/**
	 * [debugPrintRequest Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	public function debugPrintRequest($request, $name = 'REQUEST_METHOD')
	{
		$this->save_log('------  service_name : ['.$name.'] ------ ' . $this->print($request).' ', self::IS_REQUEST_LOG , $name);
	}

	/**
	 * [debugPrintResponse Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	public function debugPrintResponse($response, $name = 'RESPONSE_METHOD')
	{

		$this->save_log('------  service_name : ['.$name.'] ------ ' . $this->print($response).' ', self::IS_RESPONSE_LOG , $name);
	}

	/**
	 * [debugPrintException Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	public function debugPrintException($message)
	{
		$this->save_log('------  service_name Exception ------ ' . $this->print($message) . ' ', self::IS_ERROR_LOG);
	}

	/**
	 * [debugPrintMessage Helper method for print debug request]
	 * @param  [type] $request []
	 * @param  string $name    []
	 */
	public function debugPrintMessage($message, $name = 'CLASS_INFO')
	{
		$this->save_log('------  service_name Info ------ ' . $this->print($message).' ', self::IS_INFO_LOG);
	}
}
?>