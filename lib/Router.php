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
	 * Flag to know if a warning needs to be triggeres when a callable is not reached
	 *
	 * @access	private
	 * @var		bool
	 */
	private $warning;

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
	 * @param	bool	$warning	Flag to throw warnings
	 * @param	string	$uri		Current request uri
	 * @param	string	$method		Current request method
	 */
	public function __construct($warning = false, $uri = null, $method = null)
	{
		$this->warning = $warning;
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
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function match($method, $route, $callback, $exit = true)
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
			if ($exit) {
				exit();
			}
		}
	}

	/**
	 * Checks a route with all methods
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function all($route, $callback, $exit = true)
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
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function get($route, $callback, $exit = true)
	{
		$this->match('GET', $route, $callback);
	}

	/**
	 * Checks a route with post method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function post($route, $callback)
	{
		$this->match('POST', $route, $callback, $exit = true);
	}

	/**
	 * Checks a route with put method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function put($route, $callback)
	{
		$this->match('PUT', $route, $callback, $exit = true);
	}

	/**
	 * Checks a route with delete method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function delete($route, $callback, $exit = true)
	{
		$this->match('DELETE', $route, $callback);
	}

	/**
	 * Checks a route with options method
	 *
	 * @access	public
	 * @param	string			$route		The route
	 * @param	Closure|array	$callback	The callback to exec
	 * @param	bool			$exit		Glag to exit script if match
	 */
	public function options($route, $callback, $exit = true)
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
		if (empty($params) && \strpos($path, ':') === false) {
			list($class, $method) = $callable;
		} else {
			$subs = [':method' => $this->method];
			foreach ($params as $key => $value) {
				$subs[":{$key}"] = \str_replace('-', '_', $value);
			}
			$controller = \str_replace(array_keys($subs), $subs, $path);
			list($class, $method) = \explode('|', $controller);
		}
		if (!\class_exists($class)) {
			$this->warning($path, 'class', $class, $method);
			return;
		}
		$instance = new $class();
		if (!\method_exists($instance, $method)) {
			$this->warning($path, 'method', $class, $method);
			return;
		}
		\call_user_func_array([$instance, $method], [$params]);
	}

	/**
	 * Sends a warning when a callable is not reachable
	 *
	 * @access	private
	 * @param	string	$callable	Callable that failed to reach
	 * @param	string	$type		Callable not reached
	 * @param	string	$class		Callable classname
	 * @param	string	$method		Callable method
	 */
	private function warning($callable, $type, $class, $method)
	{
		if ($this->warning) {
			\trigger_error(
				"Not callable {$type} ({$callable}) ({$class}|{$method})",
				E_USER_WARNING
			);
		}
	}
}
