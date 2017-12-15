<?php

include_once(__DIR__ . '/SaveObjectTrait.php');

/**
 * Web2All Table SaveObjectCachedTrait trait
 *
 * This trait is for saving a Table Object using a caching layer.
 * The caching layer is only used for insert queries to do bulk inserts.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2017 Web2All BV
 * @since 2017-08-14
 */
trait Web2All_Table_SaveObjectCachedTrait {
  use Web2All_Table_SaveObjectTrait;
  
  /**
   * caching layer for DB connection
   *
   * @var Web2All_ADODB_QueryCache
   */
  protected $caching_db;
  
  /**
   * Flush all queries done by this object to the DB
   *
   */
  public function flush()
  {
    $this->caching_db->flushAllQueries();
  }
  
  /**
   * Set the querycache object (used by insert queries)
   * 
   * @param Web2All_ADODB_QueryCache $querycache
   */
  public function setQueryCacheObject($querycache)
  {
    $this->caching_db=$querycache;
  }
  
  /**
   * Insert this object into the DB
   * 
   * Inserts are cached and only flushed at object destruction,
   * unless the nocache param is true, or if a manual flush is done.
   * The caching is the default setting, but is only recommended if you
   * use this object to insert multiple records after each other. When 
   * using caching, do not use $this->getDB()->Insert_ID().
   *
   * @param boolean $nocache  [optional defaults to caching inserts]
   */
  public function insertIntoDB($nocache=false)
  {
    $query='INSERT INTO '.$this->quote($this->tablename).' (';
    $values=array();
    $fields=array();
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      
      if(isset($this->{$obj_prop})){
        if ($this->{$obj_prop} instanceof Web2All_Table_SQLOperation) {
          // if there are placeholder values, replace the placeholders with the quoted values
          
          $values[]=$this->substituteSQLOperationPlaceholders($this->{$obj_prop});
        
        }else if ($this->{$obj_prop} instanceof Web2All_Table_SQLOperationList) {
          throw new Exception("Web2All_Table_SaveObjectCachedTrait: can't insert a Web2All_Table_SQLOperationList for key value ".$obj_prop);
          
        }else{
          $values[]=$this->db->Quote($this->{$obj_prop});
        }
        
        $fields[]=$this->quote($db_field);
      }
    }
    $query.=implode(',',$fields).') VALUES ';
    if(count($fields)>0){
      $this->caching_db->doInsertQueryCached($query,$values);
    }
    if($nocache){
      $this->caching_db->flushInsertQuery($query);
    }
  }
  
}

?>