<?php

include_once(__DIR__ . '/SQLOperation.class.php');
include_once(__DIR__ . '/SQLOperationList.class.php');

/**
 * Web2All Table SaveObjectTrait trait
 *
 * This trait implements all methods for saving a Table Object.
 * 
 * ONLY use in classes already using Web2All_Table_ObjectTrait
 * because it uses properties of that trait.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2017 Web2All BV
 * @since 2017-08-14
 */
trait Web2All_Table_SaveObjectTrait {
  
  /**
   * Substitute questionmarks in SQLOperation with values
   * 
   * @param Web2All_Table_SQLOperation $sqloperation
   * @return string
   */
  protected function substituteSQLOperationPlaceholders($sqloperation)
  {
    $sql_value=$sqloperation->toSQLString();
    if (count($sqloperation->getPlaceholderValues())>0) {
      // there are placeholders
      // replace each questionmark by a placeholder value
      $startpos=0;
      $sql_value_replaced='';
      foreach ($sqloperation->getPlaceholderValues() as $avalue) {
        // find the questionmark
        $qpos=strpos($sql_value, '?', $startpos);
        // copy everything after the last question mark till this questionmark in the new string
        $sql_value_replaced.=substr( $sql_value, $startpos, $qpos );
        // append the replacement for the questionmark
        $sql_value_replaced.=$this->db->Quote($avalue);
        // start searching for questionmarks after this questionmark
        $startpos=$qpos+1;
      }
      // and add the rest of the string
      $sql_value_replaced.=substr( $sql_value, $startpos );
      $sql_value=$sql_value_replaced;
    }
    return $sql_value;
  }
  
  /**
   * Insert this object into the DB
   * 
   * @param boolean $nocache  [ignored]
   */
  public function insertIntoDB($nocache=false)
  {
    $query='INSERT INTO '.$this->quote($this->tablename).' (';
    $values=array();
    $fields=array();
    $sql_parts=array();
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      
      if(isset($this->{$obj_prop})){
        if ($this->{$obj_prop} instanceof Web2All_Table_SQLOperation) {
          // if there are placeholder values, replace the placeholders with the quoted values
          $sqloperation=$this->{$obj_prop};
          $sql_parts[]=$sqloperation->toSQLString();
          foreach ($sqloperation->getPlaceholderValues() as $avalue) {
            $values[]=$avalue;
          }
        }else if ($this->{$obj_prop} instanceof Web2All_Table_SQLOperationList) {
          throw new Exception("Web2All_Table_SaveObjectTrait: can't insert a Web2All_Table_SQLOperationList for key value ".$obj_prop);
          
        }else{
          $values[]=$this->{$obj_prop};
          $sql_parts[]='?';
        }
        
        $fields[]=$this->quote($db_field);
      }
    }
    $query.=implode(',',$fields).') VALUES ('.implode(',',$sql_parts).')';
    if(count($fields)>0){
      $this->db->Execute($query,$values);
    }
  }
  
  /**
   * Update the current item (in DB)
   *
   * @return boolean
   */
  public function updateDB()
  {
    // first check if keys are available
    if (!$this->isValid()) {
      return false;
    }
    
    if(count($this->key_properties)==0){
      // cannot update without keys
      return false;
    }
    // build where part
    $where_part='';
    foreach ($this->key_properties as $key) {
      if ($where_part) {
        $where_part.=' AND ';
      }
      if ($this->{$key} instanceof Web2All_Table_SQLOperation) {
        // please note, you really shouldn't use Web2All_Table_SQLOperation objects for key values.
        trigger_error('Web2All_Table_SaveObjectTrait->updateDB(): using Web2All_Table_SQLOperation object for key value '.$key,E_USER_NOTICE);
        $where_part.=$this->obj_to_db_trans[$key].'='.$this->{$key}->toSQLString();

      }else if ($this->{$key} instanceof Web2All_Table_SQLOperationList) {
        throw new Exception("Web2All_Table_SaveObjectTrait: can't update with a Web2All_Table_SQLOperationList in the where part for key value ".$key);
        
      }else{
        $where_part.=$this->obj_to_db_trans[$key].'='.$this->db->Quote($this->{$key});
      }
    }
    
    $update_fields=Array();
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      
      if( isset($this->{$obj_prop}) && !in_array($obj_prop,$this->key_properties) ){
        $update_fields[$db_field]=$this->{$obj_prop};
      }
      
    }
    
    // nothing to update
    if (count($update_fields)==0) {
      return false;
    }
    
    // build set part
    $set_part='';
    foreach ($update_fields as $update_field => $update_value) {
      if ($set_part) {
        $set_part.=', ';
      }
      if ($update_value instanceof Web2All_Table_SQLOperation) {
        // if there are placeholder values, replace the placeholders with the quoted values
        $set_part.=$update_field.'='.$this->substituteSQLOperationPlaceholders($update_value);

      }else if ($update_value instanceof Web2All_Table_SQLOperationList) {
        throw new Exception("Web2All_Table_SaveObjectTrait: can't update a Web2All_Table_SQLOperationListfor field ".$update_field);
      
      }else{
        $set_part.=$update_field.'='.$this->db->Quote($update_value);
      }
    }
    
    $this->db->Execute('UPDATE '.$this->quote($this->tablename).' SET '.$set_part.' WHERE '.$where_part.' ');
    
    return true;
  }
  
  /**
   * Delete the current object from the database.
   * 
   * It is required that all key properties are set.
   *
   * @return boolean
   */
  public function deleteFromDB()
  {
    // first check if keys are available
    if (!$this->isValid()) {
      return false;
    }
    
    if(count($this->key_properties)==0){
      // cannot delete without keys
      return false;
    }
    // build where part
    $where_part='';
    foreach ($this->key_properties as $key) {
      if ($where_part) {
        $where_part.=' AND ';
      }
      if ($this->{$key} instanceof Web2All_Table_SQLOperation) {
        trigger_error('Web2All_Table_SaveObjectTrait->deleteFromDB(): using Web2All_Table_SQLOperation object for key value '.$key,E_USER_NOTICE);
        $where_part.=$this->obj_to_db_trans[$key].'='.$this->{$key}->toSQLString();

      }else if ($this->{$key} instanceof Web2All_Table_SQLOperationList) {
        throw new Exception("Web2All_Table_SaveObjectTrait: can't delete using a Web2All_Table_SQLOperationList for key value ".$key);
        
      }else{
        $where_part.=$this->obj_to_db_trans[$key].'='.$this->db->Quote($this->{$key});
      }
    }
    
    $this->db->Execute('DELETE FROM '.$this->quote($this->tablename).' WHERE '.$where_part.' ');
    
    return true;
  }
  
  /**
   * Reset all properties except the keys and the given properties
   * 
   * Usefull for when you want only to update a specific field in the
   * database.
   *
   * @param array $properties
   */
  public function resetAllPropertiesExcept($properties=array())
  {
    foreach ($this->obj_to_db_trans as $obj_prop => $db_field) {
      if(  !in_array($obj_prop,$this->key_properties) && !in_array($obj_prop,$properties) ){
        $this->{$obj_prop}=null;
      }
    }
  }
  
}

?>