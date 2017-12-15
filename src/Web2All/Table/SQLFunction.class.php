<?php

Web2All_Manager_Main::loadClass('Web2All_Table_SQLOperation');

/**
 * Web2All Table SQLFunction class
 *
 * This class is for storing or comparing against SQL functions
 * with only one param, in a Table Object. 
 * 
 * examples:
 *  $user->password = $this->Web2All->Plugin->Web2All_Table_SQLFunction('PASSWORD',$password);
 *  $user->key = $this->Web2All->Plugin->Web2All_Table_SQLFunction('SHA1',$password);
 *  $tableobject->stamp = $this->Web2All->Plugin->Web2All_Table_SQLFunction('UNIX_TIMESTAMP',$datetime);
 *  $filter->datetime = $this->Web2All->Plugin->Web2All_Table_SQLFunction('FROM_UNIXTIME',time(),'<');
 *
 * @author Hans Oostendorp
 * @copyright (c) Copyright 2008-2010 Web2All BV
 * @since 2008-10-24
 */
class Web2All_Table_SQLFunction extends Web2All_Table_SQLOperation {
  
  /**
   * SQL function name
   *
   * @var string
   */
  protected $function;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param string $function  sql function name
   * @param string $param  function parameter
   * @param string $operator
   */
  public function __construct(Web2All_Manager_Main $web2all,$function,$param,$operator='=')
  {
    parent::__construct($web2all,$param,$operator);
    
    $this->function = $function;
  }
    
  /**
   * This method is called by Web2All_Table_SaveObject when
   * inserting or updating an object with a Web2All_Table_SQLOperation
   * as value.
   *
   * @return string
   */
  public function toSQLString() {
    return $this->function.'(?)';
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
    return array($this->operation);
  }
  
}

?>