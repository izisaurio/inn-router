<?php

namespace Inn;

/**
 * Gets the current uri to use on router if none given
 *
 * @author	izisaurio
 * @version	1
 */
class Uri
{
	/**
	 * Current uri value
	 *
	 * @access	private
	 * @var		string
	 */
	private $value;

	/**
	 * Construct
	 *
	 * Sets uri
	 *
	 * @access	public
	 */
	public function __construct()
	{
		$requestUri = $_SERVER['REQUEST_URI'];
		$directory = \dirname($_SERVER['SCRIPT_NAME']);
		$uri = \substr($requestUri, \strlen($directory));
		$toked = \strpos($uri, '?') !== false ? strtok($uri, '?') : $uri;
		$this->value = '/' . trim($toked, '/');
	}

	/**
	 * Returns current uri to use in router
	 *
	 * @access	public
	 * @return	string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * To string, returns value
	 *
	 * @access	public
	 * @return	string
	 */
	public function __toString()
	{
		return $this->value;
	}
}
