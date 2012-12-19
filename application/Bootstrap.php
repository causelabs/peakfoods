<?php
/**
 * Class Bootstrap
 *
 * Bootstrap.php
 *
 * Zend Framework Bootstrap
 *
 * LICENSE: Some license information
 *
 * @category   HiDef
 * @package    HiDef_Magic
 * @subpackage Wand
 * @copyright  Copyright (c) 2010 HiDef Web Inc. (http://www.hidefweb.com)
 * @version    $Id:$
 * @link       none
 * @since      File available since Release
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	private $config;
	protected $_view;
	protected $_front;
	
	protected function _initEnvironment()
	{
		$this->bootstrap('systemenvironment');
		
	}

	public function _initConfiguration() {
		$configArray = $this -> getOptions();
		//init constants
		if (isset($configArray['constants'])) {
			foreach ($configArray['constants'] as $constant_name => $value) {
				if (!defined(strtoupper($constant_name))) {
					define(strtoupper($constant_name), $value);
				}
			}
		}
		$this -> config = new Zend_Config($configArray);
		date_default_timezone_set('Etc/GMT');
		$configuration = $this -> config;
		return $configuration;
	}

	protected function _initDatabase() {
		$params = $this->getOption('resources');
		$config = new Zend_Config($params);
		$database = Zend_Db::factory($config->db);

		return $database;
	}
	
	protected function _initAuth()
	{
		$this->bootstrap('view');
		$this->bootstrap('FrontController');
		$this->_view = $this->getResource('view');
		$this->_front = $this->getResource('FrontController');
	}
	
	protected function _initViewHelpers()
	{ 
	    $view = $this->getResource('view');
		//Add helper paths
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
		//pull jQuery from Google CDN
		$view->jQuery()->setVersion("1.7.2");
		$view->jQuery()->setUiVersion("1.8.18");
		$view->jQuery()->enable();
		$view->jQuery()->uiEnable();
        $view->jQuery()->addStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/ui-lightness/jquery-ui.css');
	}

	protected function _initLogger() {
		return null;
		// base logging - for logging events pertaining to setting up enhanced logging
		$logger = new Application_Model_Logger('DEBUG', $this -> config -> log -> path . '/' . $this -> config -> log -> filename);

		// if database logging is desired, set up here and enter the db object in the NULL for logger->full()

		// enhance logging
		$logger -> full($this -> config -> log, $this -> config -> email, NULL);

		return $logger;
	}
	
	/**
	 * Doctype
	 * @return void
	 */
	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('HTML5');
	}
	protected function _initRoutes(){
		/*
        $front = Zend_Controller_Front::getInstance();
        $frontRouter = $front->getRouter();
        // default regex to catch language/version codes
        $route = new Zend_Controller_Router_Route_Regex(
        	'[v]{1}[/]+([A-Za-z0-9]+)', 
            array('controller' => 'index', 'action' => 'view'),
            array(
                1 => 'short_code'
            )
        );
        $frontRouter->addRoute('view', $route);
		 */
	}
}
