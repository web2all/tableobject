<?php

Web2All_Manager_Main::loadClass('Web2All_Table_Collection_IDataProvider');

/**
 * Web2All Table Collection simple DataProvider class
 *
 * This class defines a very basic data provider for
 * a Web2All_Table_Collection. 
 *
 * For usage example see Web2All_Table_Collection documentation.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008-2011 Web2All BV
 * @since 2008-07-22
 */
class Web2All_Table_Collection_SimpleDataProvider extends Web2All_Manager_Plugin implements Web2All_Table_Collection_IDataProvider {

  /**
   * ADODB database connection
   *
   * @var ADOConnection
   */
  protected $db;
  
  /**
   * how many records to return (truncate)
   *
   * @var int
   */
  protected $limit;
  
  /**
   * ignore the first $offset records when building resultset
   *
   * @var int
   */
  protected $offset;
  
  /**
   * the sql query
   *
   * @var string
   */
  protected $sql;
  
  /**
   * list of query placeholder values
   *
   * @var array
   */
  protected $params;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param ADOConnection $db
   */
  public function __construct(Web2All_Manager_Main $web2all,$db) {
    parent::__construct($web2all);
    
    $this->db=$db;
    
    $this->limit=-1;
    
    $this->offset=-1;
    
    $this->params=array();
  }
  
  /**
   * Assign a range to fetch from the list.
   * If you do this before calling fetchData() then
   * only results from $offset to $offset+$limit
   * are returned
   * 
   * @param int $limit
   * @param int $offset
   */
  public function setRange($limit,$offset)
  {
    $this->limit=$limit;
    $this->offset=$offset;
  }
  
  /**
   * Set the SQL to use with this query. You NEED to call this
   * method if you want to use this dataprovider.
   * 
   * It is your own responsibility to select all fields which are
   * needed for the collection object. You can use placeholders 
   * if you also use the setParams method.
   * 
   * @param string $sql
   */
  public function setSQL($sql)
  {
  	$this->sql=$sql;
  }
  
  /**
   * Set the params to use in the SQL query.
   * 
   * Params should be provided as an array of values, just
   * like you would do with the adodb Execute method.
   * 
   * @param array $params
   */
  public function setParams($params)
  {
  	$this->params=$params;
  }
  
  /**
   * Execute the query and return the adodb recordset.
   * 
   * Make sure you have at least set the sql for the query, will throw exception otherwise.
   * 
   * @return ADORecordSet
   */
  public function getADORecordSet()
  {
    if (!$this->sql) {
    	// no sql set
      throw new Exception('Web2All_Table_Collection_SimpleDataProvider::getADORecordSet: no SQL query set');
    }
    
  	return $this->db->SelectLimit($this->sql,$this->limit,$this->offset,$this->params);
  }
  
}
?>