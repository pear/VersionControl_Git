<?php

require_once 'PHPUnit/Framework.php';
require_once './Git.php';

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
    $this->assertTrue($instance instanceof VersionControl_Git, 'An instance is not of VersionControl_Git');
  }

  public function testGetCommits()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');
    $commits = $instance->getCommits();

    var_dump($commits);
  }
}

