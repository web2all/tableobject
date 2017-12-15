<?php

Web2All_Manager_Main::loadClass('Web2All_Table_ObjectTest_TableTest');

Web2All_Manager_Main::loadClass('Web2All_Table_IListableObject');
Web2All_Manager_Main::loadClass('Web2All_Table_ICountableObject');

Web2All_Manager_Main::loadClass('Web2All_Table_Object');
Web2All_Manager_Main::loadClass('Web2All_Table_SaveObject');

/**
 * Test plugin, listable, countable support caching
 *
 * @author Merijn van den Kroonenberg 
 * @copyright (c) Copyright 2017 Web2All BV
 * @since 2017-08-15
 */
class Web2All_Table_ObjectTest_TableTest3 extends Web2All_Table_ObjectTest_TableTest implements Web2All_Manager_PluginInterface,Web2All_Table_IListableObject,Web2All_Table_ICountableObject {
  use Web2All_Manager_PluginTrait; // for Web2All_Manager_PluginInterface
  use Web2All_Table_ObjectTrait { 
        isValid as public; // for Web2All_Table_IListableObject
        loadFromDBArray as public; // for Web2All_Table_IListableObject
        getRecordsetFromObjectQuery as public; // for Web2All_Table_IListableObject
        getObjectQueryCount as public;  // for Web2All_Table_ICountableObject
      }
  use Web2All_Table_SaveObjectCachedTrait;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param ADOConnection $db
   */
  public function __construct($db) 
  {
    $this->initTableObject($db);
    
    $this->tablename='table_test1';
    
    $this->obj_to_db_trans=array(
      'id' => 'tbts_id',
      'name' => 'tbts_name',
      'updated' => 'tbts_updated'
    );
    
    $this->key_properties=array('id');
  }
  
  /**
   * Set the Web2All_Manager_Main object
   *
   * @param Web2All_Manager_Main $web2all
  */
  public function setWeb2All($web2all)
  {
    $this->Web2All = $web2all;
    $this->setQueryCacheObject($this->Web2All->Plugin->Web2All_ADODB_QueryCache($this->getDB()));
  }
  
  /**
   * Load this object from the database by its (primary) key
   *
   * @return boolean
   */
  public function loadFromDB($id)
  {
    return $this->loadFromTable(array('tbts_id' => $id));
  }
  
}

?>