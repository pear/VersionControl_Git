<?php
require_once 'VersionControl/Git.php';

require_once dirname(__FILE__) . '/checkFixtures.php';

class VersionControl_Git_Object_BlobTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e');

    $this->assertTrue($instance instanceof VersionControl_Git_Object_Blob);
  }

  public function testFetch()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e');

    $this->assertTrue($instance->fetch() instanceof VersionControl_Git_Object_Blob);
  }

  public function testGetContent()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e');
    $instance->fetch();

    $this->assertEquals($instance->getContent(), 'example');
  }

  public function testToGetName()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $obj = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e', 'FILENAME');
    $obj->fetch();

    $this->assertEquals($obj->getName(), 'FILENAME');
  }
}
