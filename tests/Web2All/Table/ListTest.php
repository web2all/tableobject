<?php
require_once(__DIR__ . '/DBTestBase.php');

/**
 * @requires extension sqlite3
 */
class Web2All_Table_ListTest extends Web2All_Table_DBTestBase
{
  /**
   * setUp is run before each test method
   * 
   * Sets up the database connection in the db property
   * and populate with records
   */
  protected function setUp()
  {
    parent::setUp();
    
    $now = date('Y-m-d H:i:m');
    
    $tabletest1 = self::$Web2All->Factory->Web2All_Table_ObjectTest_TableTest1($this->db);
    $tabletest1->updated = $now;
    $basename = 'Test ';
    for($i=1;$i<=5;$i++){
      $tabletest1->name = $basename.$i;
      $tabletest1->insertIntoDB(false);
    }
    $tabletest1->flush();
  }
  
  /**
   * Test List functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @param string $listclass  Class name for objectlist
   * @dataProvider listingObjectNamesProvider
   */
  public function testListing($objectclass,$listclass)
  {
    $tabletest1_filter = self::$Web2All->Factory->{$objectclass}($this->db);
    
    $objectlist = self::$Web2All->Factory->{$listclass}($tabletest1_filter);
    if(count($objectlist)==-1){
      $this->markTestSkipped('The db driver '.$this->db->databaseType.' does not support RecordCount() so skipping test');
    }
    $this->assertCount(5, $objectlist, 'ObjectList number of fetched rows');
  }

  /**
   * Test List functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @param string $listclass  Class name for objectlist
   * @dataProvider listingObjectNamesProvider
   */
  public function testListingRange($objectclass,$listclass)
  {
    $tabletest1_filter = self::$Web2All->Factory->{$objectclass}($this->db);
    
    $objectlist = self::$Web2All->Factory->{$listclass}($tabletest1_filter);
    $objectlist->setRange(1,0);
    if(count($objectlist)==-1){
      $this->markTestSkipped('The db driver '.$this->db->databaseType.' does not support RecordCount() so skipping test');
    }
    $this->assertCount(1, $objectlist, 'ObjectList number of fetched rows');
  }

  /**
   * Test List functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @param string $listclass  Class name for objectlist
   * @dataProvider listingObjectNamesProvider
   */
  public function testIteration($objectclass,$listclass)
  {
    $tabletest1_filter = self::$Web2All->Factory->{$objectclass}($this->db);
    
    $objectlist = self::$Web2All->Factory->{$listclass}($tabletest1_filter);
    foreach($objectlist as $object){
      $this->assertEquals($objectclass, get_class($object), 'ObjectList item of correct type');
    }
  }

  /**
   * Test List functionality
   * 
   * @param string $objectclass  Class name for table objects
   * @param string $listclass  Class name for objectlist
   * @dataProvider listingObjectNamesProvider
   */
  public function testOrdering($objectclass,$listclass)
  {
    $tabletest1_filter = self::$Web2All->Factory->{$objectclass}($this->db);
    
    $objectlist = self::$Web2All->Factory->{$listclass}($tabletest1_filter);
    $objectlist->setExtra(' ORDER BY tbts_id ASC');
    $objectlist->setRange(1,4);
    foreach($objectlist as $object){
      $this->assertEquals(5, $object->id, 'id of last record (sorted)');
    }
  }

  /**
   * Test Web2All_Table_ObjectList specific functionality (asArray)
   * 
   * @param string $objectclass  Class name for table objects
   * @dataProvider tableObjectNameProvider
   */
  public function testObjectListAsArray($objectclass)
  {
    $tabletest1_filter = self::$Web2All->Factory->{$objectclass}($this->db);
    
    $objectlist = self::$Web2All->Factory->Web2All_Table_ObjectList($tabletest1_filter);
    $real_array = $objectlist->asArray();
    $this->assertCount(5, $real_array, 'ObjectList number of fetched rows');
    $this->assertEquals('array', gettype($real_array), 'is it really an array type');
    $this->assertEquals($objectclass, get_class($real_array[0]), 'ObjectList item of correct type');
  }

  /**
   * Test Web2All_Table_ObjectList specific functionality (asArray)
   * 
   * @param string $objectclass  Class name for table objects
   * @dataProvider tableObjectNameProvider
   */
  public function testObjectListAsAssocArray($objectclass)
  {
    $tabletest1_filter = self::$Web2All->Factory->{$objectclass}($this->db);
    
    $objectlist = self::$Web2All->Factory->Web2All_Table_ObjectList($tabletest1_filter);
    $objectlist->setKey('id');
    
    $this->assertTrue($objectlist->isAssoc(), 'list isAssoc()');
    
    $assoc_array=$objectlist->asAssocArray();
    
    $this->assertCount(5, $assoc_array, 'ObjectList number of fetched rows');
    
    $this->assertEquals('array', gettype($assoc_array), 'is it really an array type');
    
    $this->assertEquals($objectclass, get_class($assoc_array[1]), 'ObjectList item of correct type');
    $this->assertEquals('Test 1', $assoc_array[1]->name, 'returned object name property');
    
    $this->assertEquals($objectclass, get_class($assoc_array[5]), 'ObjectList item of correct type');
    $this->assertEquals('Test 5', $assoc_array[5]->name, 'returned object name property');
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
   * Provide table object classnames and listing object classnames
   * 
   */
  public function listingObjectNamesProvider()
  {
    return array(
      array('Web2All_Table_ObjectTest_TableTest1','Web2All_Table_ObjectList'),
      array('Web2All_Table_ObjectTest_TableTest2','Web2All_Table_ObjectList'),
      array('Web2All_Table_ObjectTest_TableTest3','Web2All_Table_ObjectList'),
      array('Web2All_Table_ObjectTest_TableTest1','Web2All_Table_ObjectIterator'),
      array('Web2All_Table_ObjectTest_TableTest2','Web2All_Table_ObjectIterator'),
      array('Web2All_Table_ObjectTest_TableTest3','Web2All_Table_ObjectIterator'),
      array('Web2All_Table_ObjectTest_TableTest3','Web2All_Table_PagedObjectList')
    );
  }
  
}
?>