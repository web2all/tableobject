<?php

/**
 * Web2All Table ObjectBaseList class
 *
 * This class manages a basic list of Web2All_Table_Object's
 * (or an extension off). It makes use of special hooks exposed 
 * in the Object.
 * This class is extended by Web2All_Table_ObjectList and Web2All_Table_ObjectIterator
 * 
 *
 * @author Hans Oostendorp
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-04-08
 */
class Web2All_Table_ObjectBaseList extends Web2All_Manager_Plugin {
  
  /**
   * the object of which to generate a list
   *
   * @var Web2All_Table_IListableObject
   */
  protected $search_obj;
  
  protected $classname;
  
  protected $extra;
  
  protected $limit;
  
  protected $offset;
  
  /**
   * Database connection
   *
   * @var ADOConnection
   */
  protected $db;
  
  /**
   * Recordset with results of DB
   *
   * @var ADORecordSet
   */
  protected $recordSet;
  
  /**
   * constructor
   * NOTE: Web2All_Table_Object $obj has to implement Web2All_Table_IListableObject
   *
   * @param Web2All_Manager_Main $web2all
   * @param Web2All_Table_Object $obj
   */
  public function __construct(Web2All_Manager_Main $web2all,$obj) {
    parent::__construct($web2all);
    
    $table_listableobject_interface='Web2All_Table_IListableObject';
    if (!($obj instanceof $table_listableobject_interface)) {
      throw new Exception('Web2All_Table_ObjectBaseList::constructor: requires a Web2All_Table_IListableObject as param');
    }
    
    $this->search_obj=$obj;

    $this->extra='';
    
    $this->limit=-1;
    
    $this->offset=-1;
    
    $this->db=$this->search_obj->getDB();
    
    $this->classname=get_class($this->search_obj);
    
    $this->recordSet = null;
    
  }

  /**
   * Assign extra sql to append to query (eg order by)
   * Do this before calling fetchData()
   * 
   * @param string $extra
   */
  public function setExtra($extra)
  {
    $this->extra=$extra;
  }
  
  /**
   * Assign a range to fetch from the list.
   * If you do this before calling fetchData() then
   * only results from $offset to $offset+$limit
   * are returned
   * 
   * @param int $limit
   * @param int $offset
   */
  public function setRange($limit,$offset)
  {
    $this->limit=$limit;
    $this->offset=$offset;
  }

  /**
   * fetch all data from the database
   * 
   * NOTE: whoever extends this method, is responsible for closing the recordSet
   */
  public function fetchData()
  {
    $this->recordSet = $this->search_obj->getRecordsetFromObjectQuery($this->extra,$this->limit,$this->offset);
    // check if recordSet instanceof ADORecordSet, to prevent PHP Fatal error and create a usefull exception with backtrace
    if (!($this->recordSet instanceof ADORecordSet))
    {
      throw new Exception("Web2All_Table_ObjectBaselist->fetchData(): recordSet is not instanceof ADORecordSet",E_USER_ERROR);
    }
  }
  
}
?>