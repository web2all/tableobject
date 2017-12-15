<?php

/**
 * Web2All Table Countable Object interface
 *
 * This interface needs to be implemented by all 
 * Web2All_Table_Object classes which should be countable
 * by the Web2All_Table_CountableObjectList.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-04-25 
 */
interface Web2All_Table_ICountableObject {
  
  /**
   * *NOTE* implementors can just call the parent (Web2All_Table_Object)
   *        method.
   * 
   * Method to query the table, based on the current values of the
   * objects properties.
   * 
   * This method is required public when implementing
   * the Web2All_Table_ICountableObject interface.
   *
   * @param string $extra  extra sql to append to query
   * @return integer
   */
  public function getObjectQueryCount($extra='');
  
}

?>