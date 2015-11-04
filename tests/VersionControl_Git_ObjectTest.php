<?php
require_once 'VersionControl/Git.php';

require_once dirname(__FILE__) . '/checkFixtures.php';

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
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $obj = new DummyGitObject($git, 'a45bb4512098e311e1e668bc73768cfaa75f9681');

    $this->assertTrue($obj instanceof VersionControl_Git_Object);
    $this->assertEquals($obj->id, 'a45bb4512098e311e1e668bc73768cfaa75f9681');
  }

  public function testToString()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $obj = new DummyGitObject($git, 'a45bb4512098e311e1e668bc73768cfaa75f9681');

    $this->assertEquals((string)$obj, 'a45bb4512098e311e1e668bc73768cfaa75f9681');
  }

  public function testToGetName()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $obj = new DummyGitObject($git, 'a45bb4512098e311e1e668bc73768cfaa75f9681', 'OBJECT_NAME');

    $this->assertEquals($obj->getName(), 'OBJECT_NAME');
  }
}

