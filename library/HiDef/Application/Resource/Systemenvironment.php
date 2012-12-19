<?php
/**
 * Zend Framework application resource for support loading application.ini type
 * application settings from the local system environment
 *
 * Takes environment variable mappings defined in application.ini and loads
 * those variables from the environment into defined mappings for other
 * resources.
 *
 * @category	HiDef_ZendStandardLibrary
 * @package		HiDef
 * @subpackage	Application_Resource
 * @copyright	Copyright (c) 2012 HiDef, Inc. (http://www.hidef.co)
 * @author		Mark Horlbeck <mark@hidef.co>
 * @version		$Id:$
 */

/**
 * Zend Framework application resource for support loading application.ini type
 * application settings from the local system environment
 *
 * Takes environment variable mappings defined in application.ini and loads
 * those variables from the environment into defined mappings for other
 * resources.
 *
 * @category	HiDef_ZendStandardLibrary
 * @package		HiDef
 * @subpackage	Application_Resource
 * @copyright	Copyright (c) 2012 HiDef, Inc. (http://www.hidef.co)
 * @author		Mark Horlbeck <mark@hidef.co>
 * @version		$Id:$
 */
class HiDef_Application_Resource_Systemenvironment extends Zend_Application_Resource_ResourceAbstract
{
	protected $_nestSeparator = '_';

	public function init()
	{
		$config = $this->getOptions();

		$namespace = $config['namespace'];
		$keys = $config['keys'];

		$bootstrap = $this->getBootstrap();

		$results = array();
		foreach ($keys as $key => $value) {
			$var = getenv($namespace.strtoupper($value));
			if ($var !== false) {
				$results = $this->_processKey($results, $key, $var);
			}
		}

		// Merge config options with existing config
		$oldConfig = new Zend_Config($bootstrap->getOptions(), true);
		$newConfig = new Zend_Config($results);

		$bootstrap->setOptions($oldConfig->merge($newConfig)->toArray());
	}

	/**
	 * Assign the key's value to the property list. Handles the
	 * nest separator for sub-properties.
	 *
	 * @param  array  $config
	 * @param  string $key
	 * @param  string $value
	 * @throws Zend_Config_Exception
	 * @return array
	 */
	protected function _processKey($config, $key, $value)
	{
		if (strpos($key, $this->_nestSeparator) !== false) {
			$pieces = explode($this->_nestSeparator, $key, 2);
			if (strlen($pieces[0]) && strlen($pieces[1])) {
				if (!isset($config[$pieces[0]])) {
					if ($pieces[0] === '0' && !empty($config)) {
						// convert the current values in $config into an array
						$config = array($pieces[0] => $config);
					} else {
						$config[$pieces[0]] = array();
					}
				} elseif (!is_array($config[$pieces[0]])) {
					/**
					 * @see Zend_Config_Exception
					 */
					require_once 'Zend/Config/Exception.php';
					throw new Zend_Config_Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
				}
				$config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
			} else {
				/**
				 * @see Zend_Config_Exception
				 */
				require_once 'Zend/Config/Exception.php';
				throw new Zend_Config_Exception("Invalid key '$key'");
			}
		} else {
			$config[$key] = $value;
		}
		return $config;
	}
}
