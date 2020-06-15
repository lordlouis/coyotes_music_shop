<?php
/**
 * @ILogger interface
 * 
 * Interface to implement in debugger class
 * and requiere a type of print to file
 * 
 */
interface iLogger
{
	/**
	 * [print Return object|array to string]
	 * @param  [abstract] $data    [Depends of implementor]
	 * @param  array  $options [Options, options for future implementors]
	 * @return [String]          [String to save in file o db]
	 */
    public function print($data, $options = []);
}
?>