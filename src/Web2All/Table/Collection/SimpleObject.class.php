<?php

Web2All_Manager_Main::loadClass('Web2All_Table_Collection_IObject');

/**
 * Web2All Table Collection simple Object class
 *
 * This class is a dynamic implementation of the
 * Web2All_Table_Collection_IObject interface.
 * And can be used as a collectionobject by the 
 * Web2All_Table_Collection class.
 *
 * basically this class behaves as an object which has properties'
 * whos names match the fields selected by the collection provider.
 * Accessing non-existing properties (fields) will throw an exception.
 * 
 * Normally you should not use this class but use Table objects (thats
 * where the Web2All_Table_Collection class is for) but this can be
 * usefull during development or when converting old code.
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008-2011 Web2All BV
 * @since 2008-07-22 
 */
class Web2All_Table_Collection_SimpleObject extends Web2All_Manager_Plugin implements Web2All_Table_Collection_IObject {
  
  protected $properties;
  
  /**
   * Initializes the Item object (properties) based on a assoc array with as keys the
   * database fields.
   *
   * @param array $db_fields
   */
  public function loadFromDBArray($db_fields){
    $this->properties=$db_fields;
  }
  
  /**
   * get the value of the property with the given name exists
   *
   * @param string $prop_name
   * @return mixed
   */
  public function getProperty($prop_name)
  {
    if (!$this->hasProperty($name)) {
      throw new Exception('Web2All_Table_Collection_SimpleObject::getProperty: property does not exist');
    }
  	return $this->properties[$prop_name];
  }
  
  /**
   * check if a property with the given name exists
   *
   * @param string $prop_name
   * @return boolean
   */
  public function hasProperty($prop_name)
  {
  	return array_key_exists($prop_name, $this->properties);
  }
  
  public function __get($name) {
    if ($this->hasProperty($name)) {
        return $this->getProperty($name);
    }
    // property does not exist
    $trace = debug_backtrace();
    trigger_error(
        'Undefined property: ' . $name .
        ' in ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line'],
        E_USER_NOTICE);
    return null;
  }
  
  /**
   * outputs all properties of the object
   *
   */
  public function debugDump()
  {
    echo "<pre>\n";
    print_r($this->properties);
    echo "</pre><br>\n";
  }
  
}

?>