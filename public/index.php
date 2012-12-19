<?php
/**
 * Main Application Page
 * 
 * index.html
 * 
 * 
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

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
	
isset($_SERVER['APPLICATION_ENV'])
	|| $_SERVER['APPLICATION_ENV']=APPLICATION_ENV;

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();