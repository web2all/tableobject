<?php

Web2All_Manager_Main::loadClass('Web2All_Table_ObjectTest_TableTest');

Web2All_Manager_Main::loadClass('Web2All_Table_IListableObject');

Web2All_Manager_Main::loadClass('Web2All_Table_Object');
Web2All_Manager_Main::loadClass('Web2All_Table_SaveObject');

/**
 * Test listable class
 *
 * @author Merijn van den Kroonenberg 
 * @copyright (c) Copyright 2017 Web2All BV
 * @since 2017-08-15
 */
class Web2All_Table_ObjectTest_TableTest2 extends Web2All_Table_ObjectTest_TableTest implements Web2All_Table_IListableObject {
  use Web2All_Table_ObjectTrait { 
        isValid as public; 
        loadFromDBArray as public; 
        getRecordsetFromObjectQuery as public; 
      }
  use Web2All_Table_SaveObjectTrait;
  
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