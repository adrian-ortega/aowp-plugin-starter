<?php

namespace AOD\Core\Helpers;

class DotNotation
{
	/**
	 * Checks to see if a key is a dot notation key
	 * @param $name
	 * @param string $needle
	 * @return bool
	 */
	protected static function _check($name, $needle = '.')
	{
		return strpos($name, $needle) !== false;
	}

	/**
	 * Returns the value of an array key using dot notation
	 * @param string $_path
	 * @param array $_data
	 * @return null
	 */
	protected static function _parse($_path, $_data)
	{
		if(empty($_path)) {
			return null;
		}

		$_field = null;

		if(self::_check($_path)) {
			$split = explode('.', $_path, 2);
			if(count($split) == 2) {
				$_path = $split[0];
				$_field = $split[1];
			}
		}

		if(isset($_data[$_path])) {
			if(!empty($_field)) {
				if(self::_check($_field)) {
					return self::_parse($_field, $_data[$_path]);
				} else if(isset($_data[$_path][$_field])) {
					return $_data[$_path][$_field];
				}
			} else {
				return $_data[$_path];
			}
		}

		return null;
	}

	/**
	 * Turns a string into an array
	 * @param $path
	 * @return array
	 */
	protected static function _split($path)
	{
		if(self::_check($path)) {
			$path = array_map('trim', explode('.', $path));
		}

		return (array) $path;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public static function dotToPath($path = '')
	{
		return implode('/', self::_split($path));
	}

	/**
	 * @param string $path
	 * @param array $data
	 * @return bool
	 */
	public static function exists($path = '', $data = [])
	{
		return is_string($path) && self::_parse($path, $data) !== null;
	}

	/**
	 * Checks to see if a path string has a specific needle
	 * @param string $path
	 * @param string $needle
	 * @return bool
	 */
	public static function pathHas($path = '', $needle = '') {

		return is_string($path) && self::_check($path, $needle);
	}

	/**
	 * Checks to see if a path begins with a specific needle
	 * @param string $path
	 * @param string $needle
	 * @return bool
	 */
	public static function beginsWith($path = '', $needle = '')
	{
		return !empty($needle) && substr(rtrim($path, '.') . '.', 0, 1) == $needle;
	}
}