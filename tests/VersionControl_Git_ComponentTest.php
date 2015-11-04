<?php
require_once 'VersionControl/Git.php';

require_once dirname(__FILE__) . '/checkFixtures.php';

class DummyGitComponent extends VersionControl_Git_Component
{
}

class VersionControl_Git_ComponentTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new DummyGitComponent($git);

    $this->assertTrue($instance instanceof VersionControl_Git_Component);
  }

  public function testConstructError()
  {
    $this->setExpectedException('PHPUnit_Framework_Error');

    $instance = new DummyGitComponent(new stdClass());
  }
}
