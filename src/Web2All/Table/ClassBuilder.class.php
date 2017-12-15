<?php
/**
 * Web2All Table Object class
 *
 * This is base class for objects reflecting a database table. All
 * kinds of support methods will be made available.
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2007-2011 Web2All BV
 * @since 2007-08-20
 */
class Web2All_Table_ClassBuilder extends Web2All_Manager_Plugin {
  
  /**
   * ADODB database connection
   *
   * @var ADOConnection
   */
  protected $db;
  
  protected $implement_listable=false;
  
  protected $keeppropnames=false;
  
  protected $saveobject=false;
  
  protected $author='';
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param ADOConnection $db
   */
  public function __construct(Web2All_Manager_Main $web2all,$db) {
    parent::__construct($web2all);
    
    $this->db=$db;
    
  }
  
  public function implementIListable()
  {
    $this->implement_listable=true;
  }
  
  /**
   * Do not remove prefix when creating property names
   * 
   */
  public function keepPropertyNames()
  {
    $this->keeppropnames=true;
  }
  
  /**
   * extend SaveObject instead of Object
   * 
   */
  public function extendSaveObject()
  {
    $this->saveobject=true;
  }
  
  /**
   * extend SaveObject instead of Object
   * @param string $author
   */
  public function setAuthor($author)
  {
    $this->author=$author;
  }
  
  /**
   * Returns a string with the class definition
   * 
   * requires template 'tableclass.tpl.php'
   *
   * @param string $tablename
   * @param string $classname
   * @return string
   */
  public function generateClass($tablename,$classname=null)
  {
    $analyzer=$this->Web2All->Plugin->Web2All_Database_Conversion_Analyzer($this->db);

    $database=$analyzer->readDatabaseTables(null,array($tablename));
    
    $table=$database->getTable($tablename);
    
    if(!$table){
      return "table not found";
    }
    
    $columns=$table->getColumns();
    
    $indices=$table->getIndices();
    
    $hasprimary=false;
    $primary_index=null;
    foreach($indices as $index){
      if($index->primary){
        $primary_index=$index;
        $hasprimary=true;
      }
    }
    
    $obj_to_db_trans=array();
    $key_properties=array();
    $property_info=array();
    
    foreach($columns as $column){
      $fieldname=$column->name;
      if($this->keeppropnames){
        $propname=$fieldname;
      }else{
        list($dummy,$propname)=explode('_',$fieldname,2);
      }
      $obj_to_db_trans[$propname]=$fieldname;
      $property_info[$propname]=array('type' => $this->getTypeFromCode($column->type), 'description' => '');
      if($column->driverspecific){
        $extradesc='';
        if($column->driverspecific->comment){
          $extradesc.=$column->driverspecific->comment;
        }
        if($column->driverspecific->type==10){
          if($extradesc){
            $extradesc.="
   * ";
          }
          $extradesc.='Possible values: '.substr($column->driverspecific->extradata,4);
        }
        if($extradesc){
          $property_info[$propname]['description'].=$extradesc;
        }
      }
      
      // check if primary key
      if ($hasprimary) {
        foreach($primary_index->columns as $prim_col){
          if($prim_col->name==$fieldname){
            // yes! its part of primary key
            $key_properties[]=$propname;
          }
        }
      }
    }
    
    /*
    // for mssql it would be:  'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME="'.$tablename.'" AND TABLE_SCHEMA="'.$this->db->database.'"'
    // and the key information:'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME="'.$tablename.'" AND TABLE_SCHEMA="'.$this->db->database.'"'
    $recordSet = $this->db->Execute('SHOW COLUMNS FROM `'.$tablename.'`',array());
    while (!$recordSet->EOF) {
      $colspec=$recordSet->FetchRow();
      $fieldname=$colspec['Field'];
      if($this->keeppropnames){
        $propname=$fieldname;
      }else{
        list($dummy,$propname)=explode('_',$fieldname,2);
      }
      $obj_to_db_trans[$propname]=$fieldname;
      if ($colspec['Key']=='PRI') {
        $key_properties[]=$propname;
      }
    }
    $recordSet->close();
    */
    
    if (is_null($classname)) {
      $classname=ucfirst($tablename);
    }
    
    $tpl=$this->Web2All->Plugin->Web2All_Template_Engine_Savant3();
    
    $tpl->assign('property_info',$property_info);
    
    $tpl->assign('obj_to_db_trans',$obj_to_db_trans);
    $tpl->assign('key_properties',$key_properties);
    $tpl->assign('tablename',$tablename);
    $tpl->assign('classname',$classname);
    $tpl->assign('author',$this->author);
    $tpl->assign('implement_listable',$this->implement_listable);
    $tpl->assign('extend_saveobject',$this->saveobject);
    $tpl->assign('date',date("Y-m-d"));
    
    $classstring="<?php\n".$tpl->fetch('tableclass.tpl.php')."\n?>";
    
    return $classstring;
  }
  
  public function getTypeFromCode($type){
    $normtype='';
    switch($type){
      case Web2All_Database_Structure_Column::TYPE_INT:
      case Web2All_Database_Structure_Column::TYPE_BIGINT:
      case Web2All_Database_Structure_Column::TYPE_NUMERIC:
        $normtype='int';
        break;
      case Web2All_Database_Structure_Column::TYPE_DOUBLE:
      case Web2All_Database_Structure_Column::TYPE_DECIMAL:
        $normtype='float';
        break;
      case Web2All_Database_Structure_Column::TYPE_VARCHAR:
      case Web2All_Database_Structure_Column::TYPE_TEXT:
        $normtype='string';
        break;
      case Web2All_Database_Structure_Column::TYPE_DATE:
        $normtype='string';
        break;
      case Web2All_Database_Structure_Column::TYPE_DATETIME:
        $normtype='string';
        break;
      case Web2All_Database_Structure_Column::TYPE_BLOB:
        $normtype='base64Binary';
        break;
      default:
        $normtype='mixed';
        break;
    }
    return $normtype;
  }
  
}

?>