<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../'));

require_once 'PHPUnit/Autoload.php';
require_once 'VersionControl/Git.php';

require_once './checkFixtures.php';

class DummyGitComponent extends VersionControl_Git_Component
{
}

class VersionControl_Git_ComponentTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitComponent($git);

    $this->assertTrue($instance instanceof VersionControl_Git_Component);
  }

  public function testConstructError()
  {
    $this->setExpectedException('PHPUnit_Framework_Error');

    $instance = new DummyGitComponent(new stdClass());
  }
}
