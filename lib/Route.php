<?php

namespace Inn;

/**
 * An added route
 * For params on route use "@" then keyword (admin/user/@id) to match ([\w\-]+)
 * Use "@@" to match (.*?)
 *
 * @author	izisaurio
 * @version	1
 */
class Route
{
	/**
	 * Route request methods
	 *
	 * @access	public
	 * @var		array
	 */
	public $methods;

	/**
	 * Route to match
	 *
	 * @access	public
	 * @var		string
	 */
	public $route;

	/**
	 * Params found on route
	 *
	 * @access	public
	 * @var		array
	 */
	public $params = [];

	/**
	 * Construct
	 *
	 * Receives the route values
	 *
	 * @access	public
	 * @param	array		$methods	Request methods available
	 * @param	string		$route		Route to match
	 */
	public function __construct(array $methods, $route)
	{
		$this->methods = $methods;
		$this->route = $route;
	}

	/**
	 * If route matches request executes callback
	 *
	 * @access	public
	 * @param	string	$uri		Current uri
	 * @param	string	$method		Current method
	 * @return	bool
	 */
	public function match($method, $uri)
	{
		if (!\in_array($method, $this->methods)) {
			return false;
		}
		if (\strpos($this->route, '@') === false) {
			return $this->route === $uri;
		}
		$route = \str_replace('@@', '(?<path>.*?)', $this->route);
		$sections = \explode('/', \ltrim($route, '/'));
		$keywords = \array_map([$this, 'mapKeywords'], $sections);
		$pattern = \join('/', $keywords);
		if (\preg_match("#^/{$pattern}$#", $uri, $matches)) {
			$this->params = \array_filter(
				$matches,
				[$this, 'filterNumeric'],
				ARRAY_FILTER_USE_KEY
			);
			return true;
		}
		return false;
	}

	/**
	 * Maps a uri section to replace with regex pattern when applicable
	 *
	 * @access	private
	 * @param	string	$value	To replace
	 * @return	bool
	 */
	private function mapKeywords($value)
	{
		if ($value[0] === '@') {
			$param = \ltrim($value, '@');
			return "(?<{$param}>[\w\-]+)";
		}
		return $value;
	}

	/**
	 * Filter useless values from regex params
	 *
	 * @access	private
	 * @param	string	$key	Key to check
	 * @return	bool
	 */
	private function filterNumeric($key)
	{
		return !\is_int($key);
	}
}
