<?php

/**
 * Web2All Table SQLOperation class
 *
 * This class is for storing special SQL operations
 * in a Table Object. It can be extended for more specific
 * operations. 
 *
 * This class does not use placeholders and performs no quoting. For
 * safely using user data, use SimpleOperator or SQLFunction instead.
 * When dealing with multi value sql functions in a filter (like BETWEEN
 * and IN, consider using the MultiValueOperator class).
 *
 * This class usually is only used as a base class.
 * 
 * examples:
 *  $filter->datetime = $this->Web2All->Plugin->Web2All_Table_SQLOperation('NOW()');
 *  $filter->name = $web2all->Plugin->Web2All_Table_SQLOperation("'test%'",'LIKE');// for use in a filter
 *  $filter->nullablefield = $web2all->Plugin->Web2All_Table_SQLOperation('NULL','IS');// for filtering on NULL values (or assigning)
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008-2010 Web2All BV
 * @since 2008-01-17
 */
class Web2All_Table_SQLOperation extends Web2All_Manager_Plugin {
  
  /**
   * store the operation
   *
   * @var string
   */
  protected $operation;
  
  /**
   * store the operator (only used in where parts)
   *
   * @var string
   */
  protected $operator;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param mixed $operation
   * @param string $operator  operator used when selecting from table
   */
  public function __construct(Web2All_Manager_Main $web2all,$operation,$operator='=') {
    parent::__construct($web2all);
    
    $this->operation=$operation;
    $this->operator=$operator;
    
  }
  
  public function __toString() {
    return $this->operation;
  }
  
  /**
   * This method is called by Web2All_Table_SaveObject when
   * inserting or updating an object with a Web2All_Table_SQLOperation
   * as value. Its also called when creating the WHERE part of a query.
   *
   * @return string
   */
  public function toSQLString() {
    return $this->operation;
  }
  
  /**
   * Get an array with all placeholder values
   * 
   * Every questionmark in the toSQLString() result must have
   * a corresponding placeholder value returned by this method.
   *
   * @return array  array of strings
   */
  public function getPlaceholderValues()
  {
    return array();
  }
  
  
  /**
   * Get the operator that has to be used when this SQLOperation
   * is used in the WHERE part of a query.
   *
   * @return string
   */
  public function getOperator()
  {
    return $this->operator;
  }
  
  /**
   * Set the operator that has to be used when this SQLOperation
   * is used in the WHERE part of a query.
   *
   * @param string $operator  sql comparison operator (=,>,<,LIKE,BETWEEN,IS)
   */
  public function setOperator($operator)
  {
    $this->operator=$operator;
  }
  
  
}

?>