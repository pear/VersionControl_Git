<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . '/checkFixtures.php';

class DummyGitComponent extends VersionControl_Git_Component
{
}

class VersionControl_Git_ComponentTest extends TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new DummyGitComponent($git);

    $this->assertTrue($instance instanceof VersionControl_Git_Component);
  }
}
