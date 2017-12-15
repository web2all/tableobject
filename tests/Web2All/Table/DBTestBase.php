<?php
use PHPUnit\Framework\TestCase;

class Web2All_Table_DBTestBase extends TestCase
{
  /**
   * Web2All framework
   *
   * @var Web2All_Manager_Main
   */
  protected static $Web2All;
  
  /**
   * ADODB database connection
   *
   * @var ADOConnection
   */
  protected $db;
  
  /**
   * sqlite_dump.db as string
   *
   * @var string
   */
  protected static $db_structure;
  
  public static function setUpBeforeClass()
  {
    self::$Web2All = new Web2All_Manager_Main();
    //self::$Web2All->DebugLevel=5;
    Web2All_Manager_Main::registerIncludeRoot(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
    self::$db_structure=file_get_contents(dirname(__FILE__) . '/ObjectTest/resources/sqlite_dump.db');
  }
  
  /**
   * setUp is run before each test method
   * 
   * Sets up the database connection in the db property
   */
  protected function setUp()
  {
    // empty database/host means create temp db on disk (as of PHP 7.0) and
    // :memory: means a fully in memory temp database
    $db_config=array(
      'type'              => 'sqlite3',
      'host'              => '',
      'database'          => ':memory:',
      'debug_queries'     => false,
      'debug_override'    => true
    );
    $this->db = self::$Web2All->Factory->Web2All_ADODB_Connection->connect($db_config);
    
    // set up db (fixture)
    
    foreach(explode(";\n",self::$db_structure) as $query){
      if(!$query){
        continue;
      }
      $this->db->Execute($query);
    }
  }
  
  /**
   * setTeardown is run each each test method
   * 
   * Disconnects from database (resulting in temp database removal)
   */
  protected function setTeardown()
  {
    $this->db->Close();
    unset($this->db);
  }
}
?>