<?php
Web2All_Manager_Main::loadClass('Web2All_Table_ObjectBaseList');

/**
 * Web2All Table ObjectList class
 *
 * This class manages a list of Web2All_Table_Object's
 * (or an extension off). It makes use of special hooks exposed 
 * in the Object.
 * 
 * Usage sample:
 *  $filter=$this->Web2All->Plugin->Web2All_Intra_Service_WebServer($this->serviceDB);
 *  $filter->centralftp='web2all';
 *  $result=$this->Web2All->Plugin->Web2All_Table_ObjectList($filter);
 *  $result->setKey('id');
 *  $result->setExtra('ORDER BY name');
 *  $result->setRange(10,0);
 *  $result->fetchData();
 *  foreach($result as $object){
 *    $object->debugDump();
 *  }
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-01-31
 */
class Web2All_Table_ObjectList extends Web2All_Table_ObjectBaseList implements Iterator,ArrayAccess,Countable {
  
  protected $key;
  
  protected $result;
  
  protected $is_assoc;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param Web2All_Table_Object $obj
   */
  public function __construct(Web2All_Manager_Main $web2all,$obj) {
    parent::__construct($web2all,$obj);
    
    $this->key=null;
    
    $this->result=null;
    
    $this->is_assoc=false;
    
  }
  
  /**
   * Assign a key to the list.
   * If you do this before calling fetchData() then
   * this list will become associative.
   * 
   * The key should be the name of a property
   *
   * @param string $key
   */
  public function setKey($key)
  {
    $this->key=$key;
    $this->is_assoc=(is_null($this->key) ? false : true);
  }

  /**
   * remove the data from the list
   */
  public function clearList()
  {
    $this->key=null;
    
    $this->result=null;
  }
  
  
  /**
   * fetch all data from the database 
   * and store the result in this object.
   * 
   * after this method has been called, this object can be
   * treated as an array of Web2All_Table_IListableObject's
   *
   */
  public function fetchData()
  {
    parent::fetchData();
    $this->is_assoc=(is_null($this->key) ? false : true);
    $this->result=array();
    while (!$this->recordSet->EOF) {
      $table_obj=clone $this->search_obj;
      $table_obj->loadFromDBArray($this->recordSet->FetchRow());
      if ($this->is_assoc) {
        $this->result[$table_obj->{$this->key}]=$table_obj;
      }else{
        $this->result[]=$table_obj;
      }
    }
    $this->recordSet->close();
  }
  
  /**
   * return the list data as an array (non assoc)
   *
   * @return array
   */
  public function asArray()
  {
    if (is_null($this->result)) {
      $this->fetchData();
    }
    if ($this->is_assoc) {
      return array_values($this->result);
    }else{
      return $this->result;
    }
  }
  
  /**
   * return the list data as an array
   * 
   * if the list is associative, the returned array
   * will be too.
   *
   * @return array
   */
  public function asAssocArray()
  {
    if (is_null($this->result)) {
      $this->fetchData();
    }
    return $this->result;
  }
  
  /**
   * Check if this list is associative
   * (a key was set with setKey())
   *
   * @return boolean
   */
  public function isAssoc()
  {
    return $this->is_assoc;
  }
  
  /**
   * check if the data was fetched from the database
   * (was fetchData() called)
   *
   * @return boolean
   */
  public function isFetched()
  {
    return (is_null($this->result) ? false :true );
  }
  
  
  /**
   * equal to reset() on an array
   * 
   * Iterator implementation
   */
  public function rewind() {
    if (is_null($this->result)) {
      $this->fetchData();
    }
    reset($this->result);
  }

  /**
   * equal to current() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function current() {
    if (is_null($this->result)) {
      $this->fetchData();
    }
    $var = current($this->result);
    return $var;
  }

  /**
   * equal to key() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function key() {
    if (is_null($this->result)) {
      $this->fetchData();
    }
    $var = key($this->result);
    return $var;
  }

  /**
   * equal to next() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function next() {
    if (is_null($this->result)) {
      $this->fetchData();
    }
    $var = next($this->result);
    return $var;
  }

  /**
   * check if the end of array is reached
   * 
   * Iterator implementation
   * 
   * @return boolean
   */
  public function valid() {
    $var = $this->current() !== false;
    return $var;
  }
  
  /**
   * Check if the key exists in the array 
   * 
   * ArrayAccess implementation
   *
   * @param mixed $offset
   * @return boolean
   */
  public function offsetExists($offset){
    
    if (is_null($this->result)) {
      $this->fetchData();
    }
    
    if(array_key_exists($offset,$this->result)){
      return true;
    }
    else{
      return false;
    }
    
  }
  
  /**
   * get value by key from the list
   * (called when using the brackets [$key])
   * 
   * ArrayAccess implementation
   *
   * @param unknown_type $offset
   * @return unknown
   */
  public function offsetGet($offset){
    
    if (is_null($this->result)) {
      $this->fetchData();
    }
    return $this->result[$offset];
    
  }
  
  /**
   * Add a new element to the array
   * 
   * ArrayAccess implementation
   *
   * @param mixed $offset
   * @param mixed $value
   * @return boolean
   */
  public function offsetSet($offset, $value){
  
    if (is_null($this->result)) {
      $this->fetchData();
    }
    if (!$this->isFetched()) {
      trigger_error('Web2All_Table_ObjectList::offsetSet: cannot set value on unitialized list',E_USER_NOTICE);
      return false;
    }
    if (!$this->is_assoc && !is_numeric($value)) {
      trigger_error('Web2All_Table_ObjectList::offsetSet: can only set numeric keys non assoc lists',E_USER_NOTICE);
      return false;
    }
    
    if ($value instanceof $this->classname) {
      $this->result[$offset]=$value;
    }else{
      trigger_error('Web2All_Table_ObjectList::offsetSet: can only add objects of type '.$this->classname.' to the list',E_USER_NOTICE);
      return false;
    }
  }
  
  /**
   * Remove element from the list
   * 
   * ArrayAccess implementation
   *
   * @param mixed $offset
   */
  public function offsetUnset($offset){
    if ($this->offsetExists($offset)) {
      unset($this->result[$offset]);
    }
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
    if (is_null($this->result)) {
      $this->fetchData();
    }
    return count($this->result);
  }

  /**
   * Method which removes all database table entries collected in this object.
   * 
   * The table objects should extend the Web2All_Table_SaveObject class.
   *
   * @throws Exception
   */
  public function removeFromDB(){
    if (is_null($this->result)) {
      $this->fetchData();
    }
    if(count($this->result) > 0){
      if(!$this->result[0] instanceof Web2All_Table_SaveObject){
        throw new Exception("Web2All_Table_ObjectList->removeFromDB: Not a saveobject, you can't delete a non saveobject.");
      }
      foreach($this->result as $row){
        $row->deleteFromDB();
      }
    }
  }
  
}

?>