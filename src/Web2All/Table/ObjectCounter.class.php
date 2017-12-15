<?php
Web2All_Manager_Main::loadClass('Web2All_Table_ObjectBaseList');

/**
 * Web2All Table ObjectCounter class
 *
 * This class counts Web2All_Table_Object's (or an extension off) in the database
 * It makes use of special hooks exposed in the Object.
 * 
 * Usage sample:
 *  $filter=$this->Web2All->Plugin->Web2All_Intra_Service_WebServer($this->serviceDB);
 *  $filter->centralftp='web2all';
 *  $counter=$this->Web2All->Plugin->Web2All_Table_ObjectCounter($filter);
 *  $counter->fetchData();// optional or if you want to make sure its not cached
 *  echo "found ".$counter->count()." records in the database";
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2014 Web2All BV
 * @since 2014-03-03
 */
class Web2All_Table_ObjectCounter extends Web2All_Manager_Plugin implements Countable {
  
  /**
   * the object of which to generate a count
   *
   * @var Web2All_Table_IListableObject
   */
  protected $search_obj;
  
  /**
   * Extra SQL to put at end of query
   *
   * @var string
   */
  protected $extra;
  
  /**
   * Database connection
   *
   * @var ADOConnection
   */
  protected $db;
  
  /**
   * Cached result count
   *
   * @var int
   */
  protected $objectcount;
  
  /**
   * constructor
   * NOTE: Web2All_Table_Object $obj has to implement Web2All_Table_IListableObject AND Web2All_Table_IListableObject
   *
   * @param Web2All_Manager_Main $web2all
   * @param Web2All_Table_Object $obj
   */
  public function __construct(Web2All_Manager_Main $web2all,$obj) {
    parent::__construct($web2all);
    
    $table_listableobject_interface='Web2All_Table_IListableObject';
    if (!($obj instanceof $table_listableobject_interface)) {
      throw new Exception('Web2All_Table_ObjectCounter::constructor: requires a Web2All_Table_IListableObject as param');
    }
    
    $table_countableobject_interface='Web2All_Table_ICountableObject';
    if (!($obj instanceof $table_countableobject_interface)) {
      throw new Exception('Web2All_Table_ObjectCounter::constructor: requires a Web2All_Table_ICountableObject as param');
    }
    
    $this->search_obj=$obj;

    $this->extra='';
    
    $this->db=$this->search_obj->getDB();
    
    $this->objectcount = null;
    
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
   * fetch all data from the database
   * 
   * 
   */
  public function fetchData()
  {
    $this->objectcount = $this->search_obj->getObjectQueryCount($this->extra);
  }
  
  /**
   * Retrieve number of elements in array
   * 
   * Countable implementation
   * 
   * *NOTE* it returns the actual amount of results returned
   *        and not the count of the results if no limit was
   *        given. If you want that, use the Web2All_Table_PagedObjectList
   *        instead.
   *
   * @return int
   */
  public function count() {
    if (is_null($this->objectcount)) {
      $this->fetchData();
    }
    return $this->objectcount;
  }
  
}

?>