<?php

namespace Inn;

use \Closure;

/**
 * Main router class
 *
 * @author	izisaurio
 * @version	1
 */
class Router
{
	/**
	 * Request uri
	 *
	 * @access	private
	 * @var		string
	 */
	private $uri;

	/**
	 * Request method
	 *
	 * @access	private
	 * @var		string
	 */
	private $method;

	/**
	 * Constructor
	 *
	 * Receives the request uri and method
	 *
	 * @access	public
	 * @param	string	$uri		Current request uri
	 * @param	string	$method		Current request method
	 */
	public function __construct($uri = null, $method = null)
	{
		$this->uri = $uri ?? (new Uri())->getValue();
		$this->method = $method ?? $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Checks a route, executes the callback on a match and terminates the script
	 * Callback can be a closure or a callable
	 *
	 * @access	public
	 * @param	mixed			$method		Request method(s) of the route
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function match($method, $route, $callback)
	{
		$methods = \is_array($method) ? $method : [$method];
		$innRoute = new Route($methods, $route);
		if ($innRoute->match($this->method, $this->uri)) {
			if (!($callback instanceof Closure) && !\is_array($callback)) {
				throw new InvalidCallbackException($route);
			}
			if ($callback instanceof Closure) {
				$callback($innRoute->params);
			} else {
				$this->toCallback($callback, $innRoute->params);
			}
		}
	}

	/**
	 * Checks a route with all methods
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function all($route, $callback)
	{
		$this->match(
			['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
			$route,
			$callback
		);
	}

	/**
	 * Checks a route with get method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function get($route, $callback)
	{
		$this->match('GET', $route, $callback);
	}

	/**
	 * Checks a route with post method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function post($route, $callback)
	{
		$this->match('POST', $route, $callback);
	}

	/**
	 * Checks a route with put method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function put($route, $callback)
	{
		$this->match('PUT', $route, $callback);
	}

	/**
	 * Checks a route with delete method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function delete($route, $callback)
	{
		$this->match('DELETE', $route, $callback);
	}

	/**
	 * Checks a route with options method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 */
	public function options($route, $callback)
	{
		$this->match('OPTIONS', $route, $callback);
	}

	/**
	 * Gets a posible dynamic callable and executes it
	 *
	 * @access	private
	 * @param	array		$callable	Array to convert
	 * @param	array		$params		Route params
	 */
	private function toCallback(array $callable, array $params = [])
	{
		$path = join('|', $callable);
		if (empty($params) || \strpos($path, ':') === false) {
			list($class, $method) = $callable;
		} else {
			$subs = [':method' => $this->method];
			foreach ($params as $key => $value) {
				$subs[":{$key}"] = $value;
			}
			$controller = \str_replace(array_keys($subs), $subs, $path);
			list($class, $method) = \explode('|', $controller);
		}
		if (!\class_exists($class)) {
			$this->warning($path);
			return;
		}
		$instance = new $class();
		if (!\method_exists($instance, $method)) {
			$this->warning($path);
			return;
		}
		\call_user_func_array([$instance, $method], [$params]);
	}

	/**
	 * Sends a warning when a callable is not reachable
	 *
	 * @access	private
	 * @param	string	$callable	Callable that failed to reach
	 */
	private function warning($callable)
	{
		\trigger_error("Not callable ({$callable})", E_USER_WARNING);
	}
}
