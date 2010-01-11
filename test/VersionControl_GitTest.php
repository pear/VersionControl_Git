<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../'));

require_once 'PHPUnit/Framework.php';
require_once 'VersionControl/Git.php';

class VersionControl_GitTest extends PHPUnit_Framework_TestCase
{
  /**
   * @expectedException PEAR_Exception
   */
  public function testConstructException()
  {
    new VersionControl_Git('!This is not valid direcotry!');
  }

  public function testConstruct()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');
    $this->assertTrue($instance instanceof VersionControl_Git);
  }

  public function testGetCommits()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');
    $commits = $instance->getCommits();

    $this->assertTrue($commits[0] instanceof VersionControl_Git_Commit);
    $this->assertEquals(count($commits), 100);

    $commits = $instance->getCommits(5);
    $this->assertEquals(count($commits), 5);
  }
}

