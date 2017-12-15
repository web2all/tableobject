<?php

/**
 * Web2All Table Collection class
 *
 * This class manages a list of Web2All_Table_Collection_Object's
 * (or an extension off). It can be used when the data retrieved is not
 * a simple Table Object.
 * 
 * Usage sample:
 *  $cprov=$this->Web2All->Plugin->Web2All_Table_Collection_SimpleDataProvider($db);
 *  $cprov->setSQL('Select * from items');
 *  $cprov->setRange(10,0);
 * 
 *  $result=$this->Web2All->Plugin->Web2All_Table_Collection($cprov,$this->Web2All->Plugin->Web2All_Table_Collection_SimpleObject());
 *  
 *  foreach($result as $object){
 *    $object->debugDump();
 *  }
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008-2011 Web2All BV
 * @since 2008-07-22
 */
class Web2All_Table_Collection extends Web2All_Manager_Plugin implements Iterator,ArrayAccess,Countable {
  
  protected $result;
  
  /**
   * the settings defining the collection
   *
   * @var Web2All_Table_Collection_IDataProvider
   */
  protected $dataprovider;
  
  /**
   * The collection object that will be cloned for each result
   * 
   * @var object
   */
  protected $collectionobject;
  
  /**
   * should this object behave like an associative array?
   * 
   * @var boolean
   */
  protected $is_assoc;
  
  /**
   * if this is an associative array, what is the key
   * (should be a property name of the collection object)
   * 
   * @var string
   */
  protected $key;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param Web2All_Table_Collection_IDataProvider $dataprovider
   * @param mixed $collectionobject
   */
  public function __construct(Web2All_Manager_Main $web2all,$dataprovider,$collectionobject=null) {
    parent::__construct($web2all);
    
    $dataprovider_interface='Web2All_Table_Collection_IDataProvider';
    $web2all->loadClass($dataprovider_interface);
    if (!($dataprovider instanceof $dataprovider_interface)) {
      throw new Exception('Web2All_Table_Collection::constructor: requires a Web2All_Table_Collection_IDataProvider as param');
    }
    
    $this->setCollectionObject($collectionobject);
    
    $this->dataprovider=$dataprovider;
    
    
    $this->is_assoc=false;
    
    $this->key=null;
    
  }
  
  /**
   * set the object which will be used to clone for each result
   * 
   * The object should implement Web2All_Table_Collection_IObject, but 
   * its not enforced, as long as its supports all the interfaces methods.
   *
   * @param Object $object
   */
  public function setCollectionObject($object)
  {
    if (!method_exists($object,'loadFromDBArray')) {
      throw new Exception('Web2All_Table_Collection::setCollectionObject: collection object does not expose the loadFromDBArray() method');
    }
    
    $this->collectionobject=$object;
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
  public function clearCollection()
  {
    $this->key=null;
    
    $this->result=null;
  }
  
  /**
   * force the retrieval of the data from the dataprovider
   *
   * @param boolean $reset  [optional, when true data will be
   *                         overwritten, and else appended]
   */
  public function collect($reset=false)
  {
    if ($reset) {
      $this->result=null;
    }
    $this->fetchData();
  }
  
  /**
   * fetch all data from the database and store the
   * result in this object.
   * 
   * after this method has been called, this object can be
   * treated as an array of objects.
   *
   */
  protected function fetchData()
  {
    $this->is_assoc=(is_null($this->key) ? false : true);
    
    if (!$this->collectionobject) {
      // no collection object;
      throw new Exception('Web2All_Table_Collection::fetchData: no collection object provide for cloning');
    }
    
    if(is_null($this->result)){
      $this->result=array();
    }
    $recordSet = $this->dataprovider->getADORecordSet();
    while (!$recordSet->EOF) {
      $coll_obj=clone $this->collectionobject;
      $coll_obj->loadFromDBArray($recordSet->FetchRow());
      if ($this->is_assoc) {
        $this->result[$coll_obj->{$this->key}]=$coll_obj;
      }else{
        $this->result[]=$coll_obj;
      }
    }
    $recordSet->close();
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
      trigger_error('Web2All_Table_Collection::offsetSet: cannot set value on unitialized list',E_USER_NOTICE);
      return false;
    }
    if (!$this->is_assoc && !is_numeric($value)) {
      trigger_error('Web2All_Table_Collection::offsetSet: can only set numeric keys non assoc lists',E_USER_NOTICE);
      return false;
    }
    
    $this->result[$offset]=$value;
    
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
   * @return int
   */
  public function count() {
    if (is_null($this->result)) {
      $this->fetchData();
    }    
    return count($this->result);
  }
  
  
}

?>