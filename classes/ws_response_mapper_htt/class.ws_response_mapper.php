<?php
abstract class ws_response_mapper
{
	protected $ws_response = [];

	protected $htt_map_object = [];

	protected $unique_column_ws_response = [];

	protected $ws_response_columns_to_map;

	protected $ws_response_unique_column;

	protected $htt_object_columns_map = [];

	protected $ws_column_unique;

	protected $ws_key_name_column_unique;

	protected $generator_ws_object;

	protected $iterator_ws_object;

	public function __construct()
	{
		$this->ws_response =[];

		$this->unique_column_ws_response = [];

		$this->htt_object_columns_map = [];

		$this->htt_map_object = [];
	}

	protected function set_wrapper_key_unique($key)
	{
	 	$this->ws_response_unique_column = [$key];

	 	$this->ws_key_name_column_unique = $key;
	}

	protected function set_columns_to_map_from_ws_response($columns = [])
	{
		$this->ws_response_columns_to_map = $columns;

		return $this;
	}

	protected function set_htt_columns_to_map($htt_columns)
	{
		$this->htt_object_columns_map = $htt_columns;

		return $this;
	}

	protected function set_ws_response($ws_response)
	{
		$this->ws_response = $ws_response;
	}

	protected function iterator_ws_to_htt_entities()
	{
		while ($this->generator_ws_object->valid()) {

		    $this->process_ws_object();

		    $this->generator_ws_object->next();
		}

		return $this;
	}

	protected function process_ws_object()
	{
		$this->map_ws_object_to_htt_object();

		return $this;
	}

	protected function map_ws_object_to_htt_object()
	{
		$ws_object = $this->generator_ws_object->current();

		$unique_key_object = array_intersect_key($ws_object, array_flip($this->ws_response_unique_column));

		$key_unique = $unique_key_object[$this->ws_key_name_column_unique];

		$this->htt_map_object[$key_unique] = [];

		$ws_object_columns = array_intersect_key($ws_object, array_flip($this->ws_response_columns_to_map));

		$this->htt_map_object[$key_unique] = array_combine($this->htt_object_columns_map, $ws_object_columns);
	}

	protected  function init_generator_ws_object()
	{
		$this->generator_ws_object = $this->generator_ws_object_response();

		return $this;
	}

	public function generator_ws_object_response()
	{
		foreach ($this->ws_response as $ws_key => $ws_object) {

			yield $ws_key => $ws_object;

		}
	}

	protected function get_htt_object_map_response()
	{
		return $this->htt_map_object;
	}
}
?>