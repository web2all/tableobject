<?php

Web2All_Manager_Main::loadClass('Web2All_Table_Object');
include_once(__DIR__ . '/SaveObjectCachedTrait.php');

/**
 * Web2All Table SaveObject class
 *
 * This class is for saving a Table Object.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2007-2017 Web2All BV
 * @since 2007-12-05
 */
abstract class Web2All_Table_SaveObject extends Web2All_Table_Object {
  use Web2All_Table_SaveObjectCachedTrait;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param ADOConnection $db
   */
  public function __construct(Web2All_Manager_Main $web2all,$db) 
  {
    parent::__construct($web2all,$db);
    
    $this->setQueryCacheObject($this->Web2All->Plugin->Web2All_ADODB_QueryCache($db));
  }
}
?>