<?php

/**
 * Web2All Table Collection Object interface
 *
 * This interface needs to be implemented by all 
 * classes which should be retrievable by the 
 * Web2All_Table_Collection class.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-07-22 
 */
interface Web2All_Table_Collection_IObject {
  
  /**
   * Initializes the Item object (properties) based on a assoc array with as keys the
   * database fields.
   *
   * @param array $db_fields
   */
  public function loadFromDBArray($db_fields);
  
}

?>