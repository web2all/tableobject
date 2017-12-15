<?php
require_once(__DIR__ . '/DBTestBase.php');

/**
 * @requires extension sqlite3
 */
class Web2All_Table_ObjectTest extends Web2All_Table_DBTestBase
{
  /**
   * Test SaveObject functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @dataProvider tableObjectNameProvider
   */
  public function testTableObject($objectclass)
  {
    $now = date('Y-m-d H:i:m');
    
    //print_r(get_included_files());
    //print_r(get_declared_classes());
    //print_r(get_declared_interfaces());
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = 'Test 1';
    $tabletest1->updated = $now;
    // insert without caching
    $tabletest1->insertIntoDB(true);
    $this->assertEquals(1, $this->db->Insert_ID(),'Expect first inserted record to have ID 1');
    $tabletest1->name = 'Test 2';
    $tabletest1->insertIntoDB(true);
    $this->assertEquals(2, $this->db->Insert_ID(),'Expect first inserted record to have ID 2');
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(1));
    $this->assertEquals('Test 1', $tabletest1_load->name,'"name" field/property mismatch after load');
    $this->assertEquals($now, $tabletest1_load->updated,'"updated" field/property mismatch after load');
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(2), 'loadFromDB(2)');
    $this->assertEquals('Test 2', $tabletest1_load->name,'"name" field/property mismatch after load');
    $this->assertEquals($now, $tabletest1_load->updated,'"updated" field/property mismatch after load');
    
    $this->assertTrue($tabletest1_load->deleteFromDB());
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertFalse($tabletest1_load->loadFromDB(2), 'loadFromDB(2)');
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(1), 'loadFromDB(1)');
    $this->assertEquals('Test 1', $tabletest1_load->name,'"name" field/property mismatch after load');
    $this->assertEquals($now, $tabletest1_load->updated,'"updated" field/property mismatch after load');
    
    //print_r(get_declared_interfaces());
    //print_r(get_included_files());
  }

  /**
   * Test SaveObject cached functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @dataProvider tableObjectCachedNameProvider
   */
  public function testTableObjectCached($objectclass)
  {
    $objectclass = 'Web2All_Table_ObjectTest_TableTest1';
    $now = date('Y-m-d H:i:m');
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = 'Test 1';
    $tabletest1->updated = $now;
    // insert without caching
    $tabletest1->insertIntoDB(false);
    $tabletest1->name = 'Test 2';
    $tabletest1->insertIntoDB(false);
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertFalse($tabletest1_load->loadFromDB(1));
    
    $tabletest1->flush();
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(1));
    $this->assertEquals('Test 1', $tabletest1_load->name,'"name" field/property mismatch after load');
    $this->assertEquals($now, $tabletest1_load->updated,'"updated" field/property mismatch after load');
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(2), 'loadFromDB(2)');
    $this->assertEquals('Test 2', $tabletest1_load->name,'"name" field/property mismatch after load');
    $this->assertEquals($now, $tabletest1_load->updated,'"updated" field/property mismatch after load');
  }

  /**
   * Test SaveObject Insert value functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @param string $value  value to insert/load
   * @dataProvider tableObjectTestValueProvider
   */
  public function testTableObjectInsertValue($objectclass,$value)
  {
    $now = date('Y-m-d H:i:m');
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = $value;
    $tabletest1->updated = $now;
    // insert without caching
    $tabletest1->insertIntoDB(true);
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(1), 'loadFromDB');
    $this->assertEquals($value, $tabletest1_load->name,'"name" field/property mismatch after load');
  }
  
  /**
   * Test SaveObject Insert value functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @param string $value  value to insert/load
   * @dataProvider tableObjectTestValueProvider
   */
  public function testTableObjectUpdateValue($objectclass,$value)
  {
    $now = date('Y-m-d H:i:m');
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = 'name';
    $tabletest1->updated = $now;
    // insert without caching
    $tabletest1->insertIntoDB(true);
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(1), 'loadFromDB');
    $tabletest1_load->name = $value;
    $this->assertTrue($tabletest1_load->updateDB(), 'Record update');
    
    $tabletest1_loadagain = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_loadagain->loadFromDB(1));
    $this->assertEquals($value, $tabletest1_loadagain->name,'"name" field/property mismatch after load');
  }
  
  /**
   * Test SaveObject Insert with SQLOperation values
   * 
   * @param string $objectclass  Class name for table objects
   * @dataProvider tableObjectNameProvider
   */
  public function testTableObjectOperation($objectclass)
  {
    $now = date('Y-m-d H:i:m');
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = 'name';
    $tabletest1->updated = self::$Web2All->Factory->Web2All_Table_SQLOperation("datetime(1092941466, 'unixepoch')");
    // insert without caching
    $tabletest1->insertIntoDB(true);
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(1), 'loadFromDB');
    $this->assertEquals('2004-08-19 18:51:06', $tabletest1_load->updated,'"updated" field/property mismatch after load');
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = 'name';
    $tabletest1->updated = self::$Web2All->Factory->Web2All_Table_SimpleOperator('2004-08-19 18:51:06');
    // insert without caching
    $tabletest1->insertIntoDB(true);
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(2), 'loadFromDB');
    $this->assertEquals('2004-08-19 18:51:06', $tabletest1_load->updated,'"updated" field/property mismatch after load');
    
    $tabletest1 = self::$Web2All->Factory->{$objectclass}($this->db);
    $tabletest1->name = self::$Web2All->Factory->Web2All_Table_SQLFunction('hex','ab-');
    $tabletest1->updated = $now;
    // insert without caching
    $tabletest1->insertIntoDB(true);
    
    $tabletest1_load = self::$Web2All->Factory->{$objectclass}($this->db);
    $this->assertTrue($tabletest1_load->loadFromDB(3), 'loadFromDB');
    $this->assertEquals('61622D', $tabletest1_load->name,'"name" field/property mismatch after load');
    
  }
  
  /**
   * Provide table object classnames
   * 
   */
  public function tableObjectNameProvider()
  {
    return array(
      array('Web2All_Table_ObjectTest_TableTest1'),
      array('Web2All_Table_ObjectTest_TableTest2'),
      array('Web2All_Table_ObjectTest_TableTest3')
    );
  }
  
  /**
   * Provide table object classnames (which support cached inserts)
   * 
   */
  public function tableObjectCachedNameProvider()
  {
    return array(
      array('Web2All_Table_ObjectTest_TableTest1'),
      array('Web2All_Table_ObjectTest_TableTest3')
    );
  }
  
  /**
   * Test Web2All_Table_ObjectTest_TableTest1
   * 
   */
  public function tableObjectTestValueProvider()
  {
    $result=array();
    $testvalues=array(
      '0',
      '1',
      '0x1',
      '000e56',
      'a',
      '"double quoted"',
      "'single quoted'",
      "it's me",
      'her & me',
      'é',
      'who are you?',
      '%',
      '%%',
      '\\',
      '(',
      '()',
      '*',
      '$',
      '$test',
      '#',
      '|',
      'CURRENT_TIMESTAMP',
      'NOW()',
      '<BR />',
      'A\';select \'B',
      'A";select "B'
    );
    foreach($this->tableObjectNameProvider() as $classname_array){
      foreach($testvalues as $testvalue){
        $result[]=array($classname_array[0],$testvalue);
      }
    }
    return $result;
  }
}
?>