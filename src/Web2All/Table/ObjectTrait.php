<?php

include_once(__DIR__ . '/SQLOperation.class.php');
include_once(__DIR__ . '/SQLOperationList.class.php');

/**
 * Web2All Table Object trait
 *
 * This trait implements most of the logic for the 
 * Web2All_Table_Object. It can also be used to turn an 
 * existing class into an table object, when you cannot
 * extend Web2All_Table_Object.
 * 
 * It also implements Web2All_Table_IListableObject and
 * Web2All_Table_ICountableObject when you make the relevant 
 * methods public.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2017 Web2All BV
 * @since 2017-08-14
 */
trait Web2All_Table_ObjectTrait {
  
  /**
   * ADODB database connection
   *
   * @var ADOConnection
   */
  protected $db;
  
  /**
   * The table name
   *
   * @var string
   */
  protected $tablename;
  
  /**
   * Assoc array with as key the property name and value the
   * database field name.
   * 
   * It is used to map the object to the database.
   *
   * @var array
   */
  protected $obj_to_db_trans;
  
  /**
   * Array with as values all properties whose db fields are 
   * part of the primary key.
   * 
   * @var array
   */
  protected $key_properties;
  
  /**
   * initialize table object
   * 
   * @param ADOConnection $db
   */
  public function initTableObject($db) 
  {
    $this->db=$db;
    
    $this->tablename='';
    
    $this->obj_to_db_trans=array();
    
    $this->key_properties=array();
  }
  
  /**
   * Initializes the Item object (properties) based on a assoc array with as keys the
   * database fields.
   *
   * @param array $db_fields
   */
  protected function loadFromDBArray($db_fields)
  {
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      $this->{$obj_prop}=$db_fields[$db_field];
    }
  }
  
  /**
   * Check if this object has been successfully loaded from the
   * database. (we assume this is, when all key properties are set)
   *
   * @return boolean
   */
  protected function isValid()
  {
    if(count($this->key_properties)==0){
      return true;
    }
    foreach ($this->key_properties as $key) {
      if(!isset($this->{$key})){
        return false;
      }
    }
    return true;
  }
  
  /**
   * Initialize this objects properties with values
   * passed on in a assoc array. The keys should correspond 
   * to the public (table related) properties.
   * 
   * All properties not passed in the array (or being null)
   * will be left alone.
   *
   * @param array $array
   */
  protected function initFromArray($array)
  {
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      if (array_key_exists($obj_prop,$array) && !is_null($array[$obj_prop])) {
        // property is in the array, assign
        $this->{$obj_prop}=$array[$obj_prop];
      }
    }
  }
  
  /**
   * Fetch a single record from the table, and initialize
   * this object with the data.
   *
   * @param array $wherefields
   * @return boolean
   */
  protected function loadFromTable($wherefields)
  {
    if (count($wherefields)==0) {
      // no where fields supplied
      return false;
    }
    
    $recordSet=$this->queryTable($wherefields,'',1);
    // check if recordSet instanceof ADORecordSet, to prevent PHP Fatal error and create a usefull exception with backtrace
    if (!($recordSet instanceof ADORecordSet))
    {
      throw new Exception("Web2All_Table_ObjectTrait->loadFromTable(): recordSet is not instanceof ADORecordSet",E_USER_ERROR);
    }
    if (!$recordSet->EOF) {
      $this->loadFromDBArray($recordSet->FetchRow());
    
      if(!$recordSet->EOF){
        // there are still results?
        // so the query did not select a single record
        trigger_error('Web2All_Table_ObjectTrait::loadFromTable(): more than one record was found. Additional records are ignored.',E_USER_NOTICE);
      }
      
      $this->onSuccessLoad();
      
      return true;
    }
    
    return false;
  }
  
  /**
   * This is called after successful load of the object from table
   * 
   * Can be used to override debugging output
   */
  protected function onSuccessLoad()
  {
    // override / extend
  }
  
  /**
   * query the table and return a recordset
   *
   * @param array $wherefields  assoc array, key is fieldname
   * @param string $extra  extra sql to append to query (eg order by, do not add WHERE)
   * @param integer $limit  max amount of results (or -1 for no limit)
   * @param integer $offset  start from position (or -1 if from start)
   * @return ADORecordSet
   */
  protected function queryTable($wherefields,$extra='',$limit=-1,$offset=-1,$selectfields='*')
  {
    // build where part
    $sqlWhere = new Web2All_Table_Object_SqlWhere();
    foreach ($wherefields as $where_field => $where_value) {
      if ($sqlWhere->part) {
        $sqlWhere->addPart(' AND ');
      }
      $sqlWhere->addSqlWhere($this->handleWhereValue($where_field,$where_value));
    }
    // $extra shouldn't contain WHERE, if $extra is not empty $sqlWhere->part has to 
    // add WHERE 1, if $sqlWhere->part is empty and $extra is empty there is no WHERE  
    if($sqlWhere->part){
      $sqlWhere->part=' WHERE '.$sqlWhere->part;
    } elseif ($extra) {
      $sqlWhere->part=' WHERE 1 ';
    }
    // when not limiting or using offset, use the Execute method, to bypass overhead.
    if($limit==-1 && $offset==-1){
      return $this->db->Execute('SELECT '.$selectfields.' FROM '.$this->quote($this->tablename).''.$sqlWhere->part.$extra,$sqlWhere->values);
    }else{
      return $this->db->SelectLimit('SELECT '.$selectfields.' FROM '.$this->quote($this->tablename).''.$sqlWhere->part.$extra,$limit,$offset,$sqlWhere->values);
    }
  }
  
  /**
   * Build where part and where value
   * 
   *  ** Recursive **
   *  
   * Builds a mysql string and array with params (encapsulated in a class) used 
   * in the WHERE part of a mysql query for one where field. This method is used
   * by queryTable for each field needed in the where. 
   * 
   * A field can have a simple string as value, of a SQLOperation. Strings and
   * SQLOperation's can be nested using SQLOperationList.
   *
   * @param string $where_field
   * @param mixed $where_value
   * @return Web2All_Table_Object_SqlWhere
   */
  protected function handleWhereValue($where_field,$where_value) {
    
    $sqlWhere = new Web2All_Table_Object_SqlWhere();

    if ($where_value instanceof Web2All_Table_SQLOperationList) {

      // We should never have an empty OperationList! This could mean we have a broken,
      // filter object, exit immediately! See mantis #0001909 for more information.
      if (count($where_value) <= 0) {
        throw new Exception("Web2All_Table_Object::handleWhereValue(): Empty SQLOperationList, this should not happen, broken filter? See mantis: #0001909");
      }

      $sqlWhere->addPart("( ");
      $suboperator = '';
      $where_value_operator = $where_value->getOperator();
      foreach ($where_value AS $where_value_part) {
        $sqlWhere->addPart(' '.$suboperator.' ');
        $sqlWhere->addSqlWhere($this->handleWhereValue($where_field,$where_value_part));
        $suboperator = $where_value_operator;
      }
      $sqlWhere->addPart(" )");

    } else if ($where_value instanceof Web2All_Table_SQLOperation) {
      $sqlWhere->addPart($this->quote($where_field).' '.$where_value->getOperator().' '.$where_value->toSQLString());
      foreach ($where_value->getPlaceholderValues() as $avalue) {
        $sqlWhere->addValue($avalue);
      }
      
    }else{
      $sqlWhere->addPart($this->quote($where_field).' = ?');
      $sqlWhere->addValue($where_value);
    }
    return $sqlWhere;
  }
  
  /**
   * Build a hash with all currectly set fields of this object
   * The key is the db field name and the value is the property value
   *
   * @return array
   */
  protected function getDBFieldValues()
  {
    $wherefields=array();
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      
      if(isset($this->{$obj_prop})){

        $wherefields[$db_field]=$this->{$obj_prop};
      }
    }
    return $wherefields;
  }
  
  /**
   * Method to query the table, based on the currecnt values of the
   * objects properties.
   * 
   * This method is required public when implementing
   * the Web2All_Table_IListableObject interface.
   *
   * @param string $extra  extra sql to append to query (eg order by)
   * @param integer $limit  max amount of results (or -1 for no limit)
   * @param integer $offset  start from position (or -1 if from start)
   * @return ADORecordSet
   */
  protected function getRecordsetFromObjectQuery($extra='',$limit=-1,$offset=-1)
  {
    $wherefields=$this->getDBFieldValues();
    return $this->queryTable($wherefields,$extra,$limit,$offset);
  }
  
  /**
   * Method to query the table, based on the currecnt values of the
   * objects properties.
   * 
   * This method is required public when implementing
   * the Web2All_Table_IListableObject interface.
   *
   * @param string $extra  extra sql to append to query (eg order by)
   * @param integer $limit  max amount of results (or -1 for no limit)
   * @param integer $offset  start from position (or -1 if from start)
   * @return ADORecordSet
   */
  protected function getObjectQueryCount($extra='')
  {
    $wherefields=$this->getDBFieldValues();
    $recordSet=$this->queryTable($wherefields,$extra,-1,-1,'COUNT(*) AS resultcount');
    $count=-1;
    if (!$recordSet->EOF) {
      $fields=$recordSet->FetchRow();
      $count=$fields['resultcount'];
    }
    return $count;
  }
  
  
  /**
   * Return all properties and their values of this object 
   * as an assoc array.
   *
   * @return array
   */
  protected function toArray()
  {
    $properties=Array();
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      
      if(isset($this->{$obj_prop})){
        $properties[$obj_prop]=$this->{$obj_prop};
      }else{
        $properties[$obj_prop]=null;
      }
    }
    return $properties;
  }
  
  /**
   * Reset all public properties (the ones which
   * correspond to table fields)
   *
   * reset properties will be assigned the null value.
   */
  public function resetValues()
  {
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      $this->{$obj_prop}=null;
    }
  }
  
  /**
   * returns all properties of the object as tring
   * 
   */
  public function asDebugString()
  {
    return preg_replace('/^Array/',get_class($this),print_r($this->toArray(),true));
  }
  
  /**
   * set the database handle (usually done in constructor)
   *
   * @param ADOConnection $adodb_handle
   */
  public function setDB($adodb_handle)
  {
    $this->db=$adodb_handle;
  }
  
  /**
   * get the adodb handle used by this object
   *
   * @return ADOConnection
   */
  public function getDB()
  {
    return $this->db;
  }
  
  /**
   * Get quoted tablename or fieldname
   *
   * @param string $name
   * @return string
   */
  protected function quote($name)
  {
    if($this->db->nameQuote=='['){
      return '['.$name.']';// mssql has [ and ] for quoting
    }else{
      return $this->db->nameQuote.$name.$this->db->nameQuote;
    }
  }
  
}

/**
 * Data container for SqlWhere (part and value)
 *
 * @author Hans Oostendorp
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-07-09
 */
class Web2All_Table_Object_SqlWhere
{
  public $part = '';
  public $values = array();
  
  /**
   * Add new part to existing part
   *
   * @param string $part
   */
  public function addPart($part)
  {
    $this->part.= $part;
  }
  
  /**
   * Add new value to existing values
   *
   * @param string $value
   */
  public function addValue($value)
  {
    $this->values[] = $value;
  }
  
  /**
   * Add other SqlWhere to this SqlWhere
   *
   * @param Web2All_Table_Object_SqlWhere $sqlWhere
   */
  public function addSqlWhere($sqlWhere)
  {
    if (!($sqlWhere instanceof Web2All_Table_Object_SqlWhere))
    {
      throw new Exception("Web2All_Table_Object_SqlWhere: param sqlWhere isn't instanceof Web2All_Table_Object_SqlWhere");
    }
    $this->addPart($sqlWhere->part);
    $this->values = array_merge($this->values,$sqlWhere->values);
  }
}

?>