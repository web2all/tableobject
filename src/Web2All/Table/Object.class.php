<?php

Web2All_Manager_Main::loadClass('Web2All_Table_SQLOperation');
include_once(__DIR__ . '/ObjectTrait.php');

/**
 * Web2All Table Object class
 *
 * This is base class for objects reflecting a database table. All
 * kinds of support methods will be made available.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2007-2017 Web2All BV
 * @since 2007-08-20
 */
abstract class Web2All_Table_Object extends Web2All_Manager_Plugin {
  use Web2All_Table_ObjectTrait;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param ADOConnection $db
   */
  public function __construct(Web2All_Manager_Main $web2all,$db) 
  {
    parent::__construct($web2all);
    
    $this->initTableObject($db);
  }
  
  /**
   * This is called after successful load of the object from table
   * 
   * Can be used to override debugging output
   */
  protected function onSuccessLoad()
  {
    if ($this->Web2All->DebugLevel>Web2All_Manager_Main::DEBUGLEVEL_MEDIUM) {
      $this->Web2All->debugLog('Web2All_Table_Object::loadFromTable(): loaded: '.$this->asDebugString());
    }
  }
  
  /**
   * outputs all properties of the object
   *
   */
  public function debugDump()
  {
    $this->Web2All->debugLog($this->asDebugString());
  }
  
}

?>