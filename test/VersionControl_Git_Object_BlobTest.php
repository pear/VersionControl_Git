<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../'));

require_once 'PHPUnit/Autoload.php';
require_once 'VersionControl/Git.php';

require_once './checkFixtures.php';

class VersionControl_Git_Object_BlobTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e');

    $this->assertTrue($instance instanceof VersionControl_Git_Object_Blob);
  }

  public function testFetch()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e');

    $this->assertTrue($instance->fetch() instanceof VersionControl_Git_Object_Blob);
  }

  public function testGetContent()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Blob($git, '33a9488b167e4391ad6297a1e43e56f7ec8a294e');
    $instance->fetch();

    $this->assertEquals($instance->getContent(), 'example');
  }
}
