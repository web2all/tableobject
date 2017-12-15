<?php

Web2All_Manager_Main::loadClass('Web2All_Table_ObjectIterator');

/**
 * Web2All Table Paged ObjectIterator class
 *
 * This class iterates through the database results
 * (or an extension off). It makes use of special hooks exposed 
 * in the Object.
 * 
 * Usage sample:
 *  $filter=$this->Web2All->Plugin->Web2All_Intra_Service_WebServer($this->serviceDB);
 *  $filter->centralftp='web2all';
 *  $result=$this->Web2All->Plugin->Web2All_Table_PagedObjectIterator($filter);
 *  $result->setPageSize(30);
 *  $result->setPage(1);
 *  $result->setOrderBy('name ASC');
 *  $result->fetchData();
 *  foreach($result as $object){
 *    $object->debugDump();
 *  }
 *  echo "displaying page ".$result->getPage()." of ".$this->getTotalPages();
 *
 * @author Hans Oostendorp
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-07-08
 */
class Web2All_Table_PagedObjectIterator extends Web2All_Table_ObjectIterator {
  
  protected $pagesize;
  
  protected $resultcount;
  
  protected $orderby;
  
  protected $additionalextra;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param Web2All_Table_Object $obj
   */
  public function __construct(Web2All_Manager_Main $web2all,$obj) {
    parent::__construct($web2all,$obj);
    $table_countableobject_interface='Web2All_Table_ICountableObject';
    if (!($obj instanceof $table_countableobject_interface)) {
      throw new Exception('Web2All_Table_PagedObjectIterator::constructor: requires a '.$table_countableobject_interface.' as param');
    }
    $this->setPageSize(20);
    $this->orderby='';
  }
  
  /**
   * Gets how much results are on each page
   *
   * @return integer
   */
  public function getPageSize()
  {
    return $this->pagesize;
  }
  
  /**
   * Gets the current page number
   *
   * @return integer
   */
  public function getPage()
  {
    return floor($this->offset/$this->pagesize)+1;
  }
  
  /**
   * Gets the amount of pages
   *
   * @return integer
   */
  public function getTotalPages()
  {
    return ceil($this->getCount()/$this->pagesize);
  }
  
  /**
   * Sets how much results should be on each page
   *
   * @param integer $count
   */
  public function setPageSize($count)
  {
    $this->pagesize=$count;
  }
  
  /**
   * Sets which page to get.
   * 
   * Numbering starts at page 1
   *
   * @param integer $number
   */
  public function setPage($number)
  {
    $this->setRange($this->pagesize,($number-1)*($this->pagesize));
  }
  
  /**
   * get the amount of results which were found
   * (ignoring pagination)
   * 
   * @return integer
   */
  public function getCount(){
    if(is_null($this->resultcount)){
      $this->resultcount = $this->search_obj->getObjectQueryCount($this->additionalextra);
    }
    
    return $this->resultcount;
    
  }
  
  
  /**
   * remove the data from the list
   */
  public function clearList()
  {
    parent::clearList();
    
    $this->resultcount=null;
  }
  
  /**
   * Assign extra sql to append to query
   * Do this before calling fetchData()
   * 
   * *NOTE* for sorting, use setOrderBy() and not this method
   * 
   * @param string $extra
   */
  public function setExtra($extra)
  {
    $this->additionalextra=$extra;
  }
  
  /**
   * Sort the result
   * Do this before calling fetchData()
   * 
   * *NOTE* do not include the string 'ORDER BY' in the param (implicit).
   * 
   * @param string $order  order by value.
   */
  public function setOrderBy($order='')
  {
    if(!$order){
      $this->orderby='';
    }else{
      $this->orderby=' ORDER BY '.$order;
    }
  }
  
  /**
   * fetch all data from the database and store the
   * result in this object.
   * 
   * after this method has been called, this object can be
   * treated as an array of Web2All_Table_IListableObject's
   *
   */
  public function fetchData()
  {
    $this->extra=$this->additionalextra.$this->orderby;
    parent::fetchData();
  }
  
}

?>