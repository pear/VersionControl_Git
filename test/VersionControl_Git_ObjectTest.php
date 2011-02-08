<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../'));

require_once 'PHPUnit/Autoload.php';
require_once 'VersionControl/Git.php';

require_once './checkFixtures.php';

class DummyGitObject extends VersionControl_Git_Object
{
  public function fetch()
  {
    return $this;
  }
}

class VersionControl_Git_ObjectTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $obj = new DummyGitObject($git, 'a45bb4512098e311e1e668bc73768cfaa75f9681');

    $this->assertTrue($obj instanceof VersionControl_Git_Object);
    $this->assertEquals($obj->id, 'a45bb4512098e311e1e668bc73768cfaa75f9681');
  }

  public function testToString()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $obj = new DummyGitObject($git, 'a45bb4512098e311e1e668bc73768cfaa75f9681');

    $this->assertEquals((string)$obj, 'a45bb4512098e311e1e668bc73768cfaa75f9681');
  }
}

