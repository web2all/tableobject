<?php

Web2All_Manager_Main::loadClass('Web2All_Table_SQLOperation');

/**
 * Web2All Table SimpleOperator class
 * 
 * Can be used for all the normal boolean operators. It extends
 * SQLOperation with variable placeholders (and thus escaping params).
 * Its main use is in filters for objectlists (where part of a query).
 * When you want to use SQL functions, consider using the SQLFunction class.
 * 
 * examples:
 *  $filter->datetime = $this->Web2All->Plugin->Web2All_Table_SimpleOperator($datetime,'>');
 *  $filter->id = $web2all->Plugin->Web2All_Table_SimpleOperator($id,'!=');
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008-2010 Web2All BV
 * @since 2008-12-08
 */
class Web2All_Table_SimpleOperator extends Web2All_Table_SQLOperation {
  
  protected $value;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param array $param
   * @param string $operator
   */
  public function __construct(Web2All_Manager_Main $web2all,$param,$operator='=')
  {
    
    // validate operator params
    $this->value=$param;
    
    parent::__construct($web2all,'?',$operator);
    
  }
  
  public function __toString() {
    return $this->value;
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
    return array($this->value);
  }
  
}

?>