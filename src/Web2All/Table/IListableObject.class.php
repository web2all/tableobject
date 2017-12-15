<?php

/**
 * Web2All Table Listable Object interface
 *
 * This interface needs to be implemented by all 
 * Web2All_Table_Object classes which should be listable
 * by the Web2All_Table_ObjectList.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-01-31 
 */
interface Web2All_Table_IListableObject {
  
  /**
   * *NOTE* implementors can just call the parent (Web2All_Table_Object)
   *        method.
   * 
   * Method to query the table, based on the currecnt values of the
   * objects properties.
   * 
   * This method is required public when implementing
   * the Web2All_Table_IListableObject interface.
   *
   * @param string $extra  extra sql to append to query (eg order by)
   * @param integer $limit  max amount of results (or -1 for no limit)
   * @param integer $offset  start from position (or -1 if from start)
   * @return ADORecordSet
   */
  public function getRecordsetFromObjectQuery($extra='',$limit=-1,$offset=-1);
  
  /**
   * *NOTE* implementors can just call the parent (Web2All_Table_Object)
   *        method.
   * 
   * Initializes the Item object (properties) based on a assoc array with as keys the
   * database fields.
   *
   * @param array $db_fields
   */
  public function loadFromDBArray($db_fields);
  
  public function getDB();
  
}

?>