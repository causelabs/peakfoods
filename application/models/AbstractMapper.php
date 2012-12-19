<?php

/**
 * Class Application_Model_AbstractMapper
 * 
 * AbstractMapper.php
 * 
 * Abstract Mapper
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

abstract class Application_Model_AbstractMapper extends Zend_Db
{
  protected $db;

  protected $role;
  protected $resourceGroup;
  
  public function __construct( $db  ) 
  {
    $this->db = $db;
    $this->db->setFetchMode(Zend_Db::FETCH_OBJ);
  }
}