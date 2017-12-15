<?php

Web2All_Manager_Main::loadClass('Web2All_Table_SQLOperation');

/**
 * Web2All Table MultiValueOperator class
 * 
 * Can be used for the following operators: IN, BETWEEN
 * 
 * *NOTE* This class CANNOT be used for save operations (comparison only)
 *
 * This class handles the special mysql IN and BETWEEN operator.
 * 
 * The IN operator expects a list of params which will be used in the
 * following way:
 * $tableobject->fieldname=$web2all->Web2All_Table_MultiValueOperator(array('param1','param2','param<n>'),'IN');
 * WHERE fieldname IN ('param1','param2','param<n>')
 * 
 * The BETWEEN operator expects two params which will be used in the
 * following way:
 * $tableobject->fieldname=$web2all->Web2All_Table_MultiValueOperator(array('param1','param2'),'BETWEEN');
 * WHERE fieldname BETWEEN 'param1' AND 'param2'
 * 
 * At the moment only these two operators are supported.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-12-08
 */
class Web2All_Table_MultiValueOperator extends Web2All_Table_SQLOperation {
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param array $params  nested operations not yet supported
   * @param string $operator
   */
  public function __construct(Web2All_Manager_Main $web2all,$params,$operator='IN')
  {
    
    // validate operator params
    $this->checkParams($params,$operator);
    
    parent::__construct($web2all,$params,$operator);
    
  }
  
  /**
   * Checks if params are correct and throws exception if not
   *
   * @param array $params
   * @param string $operator
   */
  protected function checkParams($params,$operator)
  {
    if (!is_array($params)) {
    	throw new Exception('Web2All_Table_MultiValueOperator: first param must be an array');
    }
    if ($operator=='IN') {
    	if (count($params)<1) {
    		throw new Exception('Web2All_Table_MultiValueOperator: IN operator requires at least one param');
    	}
    } elseif ($operator=='BETWEEN') {
    	if (count($params)!=2) {
    		throw new Exception('Web2All_Table_MultiValueOperator: BETWEEN operator requires exactly two params');
    	}
    }else{
      throw new Exception('Web2All_Table_MultiValueOperator: unsupported operator "'.$operator.'"');
    }
  }
  
  /**
   * This method is called by Web2All_Table_SaveObject when
   * inserting or updating an object with a Web2All_Table_SQLOperation
   * as value.
   *
   * @return string
   */
  public function toSQLString() {
    if ($this->operator=='IN') {
    	$sql = '(';
	    $komma = '';
	    for ($i=0;$i<count($this->operation);$i++)
	    {
	      $sql.= $komma.'?';
	      $komma = ',';
	    }
	    $sql.= ')';
	    return $sql;
    } elseif ($this->operator=='BETWEEN') {
    	return '? AND ?';
    }
  }

  public function __toString() {
    // This object shouldn't be converted to string, its not a meaningull value
    trigger_error('Web2All_Table_MultiValueOperator: illegal conversion to string',E_USER_WARNING);
    return $this->getOperator().'('.implode(',',$this->operation).')';
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
    return $this->operation;
  }
  
  /**
   * Set the operator that has to be used when this SQLOperation
   * is used in the WHERE part of a query.
   *
   * @param string $operator  sql comparison operator (IN,BETWEEN)
   */
  public function setOperator($operator)
  {
    $this->checkParams($this->operation,$operator);
    $this->operator=$operator;
  }
  
}

?>