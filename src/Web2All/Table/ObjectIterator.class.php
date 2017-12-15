<?php
Web2All_Manager_Main::loadClass('Web2All_Table_ObjectBaseList');

/**
 * Web2All Table ObjectIterator class
 *
 * This class iterates through the database results
 * 
 * Usage sample:
 *  $filter=$this->Web2All->Plugin->Web2All_Intra_Service_WebServer($this->serviceDB);
 *  $filter->centralftp='web2all';
 *  $results=$this->Web2All->Plugin->Web2All_Table_ObjectIterator($filter);
 *  $results->setExtra('ORDER BY name');
 *  $results->setRange(10,0);
 *  echo "numrows: ".count($results);
 *  foreach ($results AS $object)
 *  {
 *    $object->debugDump();
 *  }
 *
 * @author Hans Oostendorp
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-04-08
 */
class Web2All_Table_ObjectIterator extends Web2All_Table_ObjectBaseList implements Iterator,Countable
{
  
  /**
   * Current table object
   * Is null when there is no current object
   *
   * @var Web2All_Table_Object
   */
  protected $current_table_obj;
  
  /**
   * Current rownr [0-n]
   * Note: first row nr is one! not zero! 
   * row nr is only zero when query hasn't been executed yet
   *
   * @var int
   */
  protected $current_row_nr;

  /**
   * Number of records after fetchData is executed
   *
   * @var int
   */
  protected $recordcount;
  
  /**
   * Keeps track if the recordSet is used or not. The query can be executed
   * for example by count(), but the recordSet isn't used yet, so wen rewind()
   * is called after count() the query doesn't need to be executed again.
   *
   * @var boolean
   */
  protected $recordSet_used;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param Web2All_Table_Object $obj
   */
  public function __construct(Web2All_Manager_Main $web2all,$obj) {
    parent::__construct($web2all,$obj);
    
    $this->current_table_obj = null;
    $this->current_row_nr = null;
    $this->recordcount = null;
    $this->recordSet_used = null;
  }

  /**
   * fetch all data from the database 
   * 
   * after this method has been called, this object can be
   * iterated as an array of Web2All_Table_IListableObject's
   *
   */
  public function fetchData()
  {
    // start row nr with zero, fetchData is called by rewind and followed by next
    // when de first row is fetched by next, the row nr is increased by one
    $this->current_row_nr = 0;
    // perform the query
    parent::fetchData();
    
    // reset recordcount
    $this->recordcount = null;
    
    // get first row, its expected
    $this->next();
    // because this is the first time, we consider the recordset unused
    // the current object is the first row, just what we expect when calling rewind, 
    // so rewind doesn't have to call fetchData again when its in this state
    // our previous next call marked the recordSet as used, but it doesn't know
    // its called for the first time, we do, so we mark the recordSet as unused now
    $this->recordSet_used = false;
  }
  
  /**
   * equal to reset() on an array
   * 
   * Iterator implementation
   */
  public function rewind()
  {
    // start the query if recordSet is not yet initialised 
    // or start the query again if recordSet is initialised but the recordSet is used
    if (is_null($this->recordSet) || $this->recordSet_used)
    {
      // if recordSet is initialised, close it
      if (!is_null($this->recordSet))
      {
        $this->recordSet->Close();
      }
      $this->fetchData();
    }
  }

  /**
   * equal to current() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function current()
  {
    return $this->current_table_obj;
  }

  /**
   * equal to key() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function key()
  {
    // Note: row nr's start counting from one
    // Array key's start counting from zero
    $key = $this->current_row_nr-1;
    return $key;
  }

  /**
   * equal to next() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function next()
  {
    // there is no next when recordSet isn't initialised yet, or when there are
    // no more records
    if (is_null($this->recordSet) || $this->recordSet->EOF)
    {
      // there is no recordSet yet, or there is no data
      $this->current_row_nr = 0;
      $this->current_table_obj = null;
      // if recordSet is initialised, close it
      if (!is_null($this->recordSet))
      {
        $this->recordSet->Close();
      }
    }
    else
    {
      // keep track of row nr, for key method
      $this->current_row_nr++;
      // fill current object with data from current row
      $this->current_table_obj = clone $this->search_obj;
      $this->current_table_obj->loadFromDBArray($this->recordSet->FetchRow());
      // remember that we used the recordSet now, so when rewind() is called again the query has to be executed again
      $this->recordSet_used = true;
    }
    // normally next returns nothing, but sometimes its easy to have the current object
    return $this->current_table_obj;
  }

  /**
   * check if the end of array is reached
   * The end of the array is reached when fetching the next row fails, in that
   * case the current object is null
   * 
   * Iterator implementation
   * 
   * @return boolean
   */
  public function valid() 
  {
    if (is_null($this->current_table_obj))
    {
      return false;
    }
    return true;
  }

  /**
   * Get the recordcount
   * 
   * Countable implementation
   *
   * @return int
   */
  public function count()
  {
    // start the query if recordSet is not yet initialised 
    // if the recordSet is initialised it doesn't matter if its used or not
    if (is_null($this->recordSet))
    {
      $this->fetchData();
    }
    if(is_null($this->recordcount)){
      // store the number of records returned
      $this->recordcount = $this->recordSet->RecordCount();
    }
    return $this->recordcount;
  }
}
?>