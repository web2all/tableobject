<?php

/**
 * Web2All Table Collection DataProvider interface
 *
 * This describes the interface for data providers for
 * Web2All_Table_Collection's. 
 * 
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-07-22
 */
interface Web2All_Table_Collection_IDataProvider {
  
  /**
   * return a recordset for the collection
   *
   * @return ADORecordSet
   */
  public function getADORecordSet();
  
}
?>