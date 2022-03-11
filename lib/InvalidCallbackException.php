<?php

namespace Inn;

use \Exception;

/**
 * Invalid callback
 *
 * @author	izisaurio
 * @version	1
 */
class InvalidCallbackException extends Exception
{
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	string	$route	Route trying to reach
	 */
	public function __construct($route)
	{
		parent::__construct("Invalid callback for route ({$route})");
	}
}
