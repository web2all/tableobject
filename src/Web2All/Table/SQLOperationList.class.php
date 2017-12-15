<?php

/**
 * Web2All Table SQLOperationList class
 *
 * This class is for storing a list of SQL operations
 * in a Table Object.
 *
 * Examples:
 * 
 *   $operations = array();
 *   foreach(explode(' ',$searchwords) AS $searchword)
 *   {
 *     $operations[] = $searchword;
 *   }
 *   $tableobject->fieldname = $this->Web2All->Plugin->Web2All_Table_SQLOperationList($operations,'OR');
 *
 *   $operations = array();
 *   foreach(explode(' ',$searchwords) AS $searchword)
 *   {
 *     $operations[] = $this->Web2All->Plugin->Web2All_Table_SQLOperation("'%$searchword%'",'LIKE');
 *   }
 *   $tableobject->fieldname = $this->Web2All->Plugin->Web2All_Table_SQLOperationList($operations,'OR');
 *  
 * SQLOperationList supports nesting
 * 
 * @author Hans Oostendorp
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-07-09
 */
class Web2All_Table_SQLOperationList extends Web2All_Manager_Plugin implements Iterator,Countable
{
  
  /**
   * Array of operations
   *
   * @var array
   */
  protected $operations;
  
  /**
   * Operator
   *
   * @var string
   */
  protected $operator;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param array $operations
   * @param string $operator
   */
  public function __construct(Web2All_Manager_Main $web2all,$operations,$operator='OR')
  {
    parent::__construct($web2all);
    
    if (!is_array($operations))
    {
      throw new Exception('Web2All_Table_SQLOperationList: operations must be an array');
    }
        
    $this->operations=$operations;
    $this->operator=$operator;
  }  
  
  /**
   * Get the operator that has to be used when this SQLOperationList
   * is used in the WHERE part of a query.
   *
   * @return string
   */
  public function getOperator()
  {
    return $this->operator;
  }

  /**
   * equal to reset() on an array
   * 
   * Iterator implementation
   */
  public function rewind() {
    reset($this->operations);
  }

  /**
   * equal to current() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function current() {
    $var = current($this->operations);
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
    $var = key($this->operations);
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
    $var = next($this->operations);
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
   * Retrieve number of elements in array
   * 
   * Countable implementation
   * 
   * @return int
   */
  public function count() {
    return count($this->operations);
  }
}

?>