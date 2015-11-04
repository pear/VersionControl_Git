<?php
require_once 'VersionControl/Git.php';

require_once dirname(__FILE__) . '/checkFixtures.php';

class VersionControl_Git_Object_TreeTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');

    $this->assertTrue($instance instanceof VersionControl_Git_Object_Tree);
  }

  public function testFetch()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');

    $this->assertTrue($instance->fetch() instanceof VersionControl_Git_Object_Tree);
  }

  public function testSeek()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $instance->seek(1);
    $this->assertEquals((string)$instance->current(), 'b02de46733580a2d82931639b0f2dedef1a43fa5');

    $instance->seek(2);
    $this->assertEquals((string)$instance->current(), 'f0614972142afd3974395df8709688749dd2a224');
  }

  public function testSeekException()
  {
    $this->setExpectedException('VersionControl_Git_Exception');

    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $instance->seek(100000);
  }

  public function testRewind()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $instance->seek(2);
    $this->assertEquals((string)$instance->current(), 'f0614972142afd3974395df8709688749dd2a224');

    $instance->rewind();
    $this->assertEquals((string)$instance->current(), '18f7b86f8a0e9d608cafd641efb29c54854aeefe');
  }

  public function testKey()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $instance->seek(2);
    $this->assertEquals($instance->key(), 2);
  }

  public function testNext()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $instance->next();
    $this->assertEquals($instance->key(), 1);

    $instance->next();
    $this->assertEquals($instance->key(), 2);
  }

  public function testValid()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $instance->next();
    $this->assertTrue($instance->valid());
    $instance->next();
    $instance->next();
    $this->assertFalse($instance->valid());
  }

  public function testToGetName()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $obj = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261', 'TREE_NAME');

    $this->assertEquals($obj->getName(), 'TREE_NAME');
  }

  public function testAllObjectsHasName()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Tree($git, 'cd0762280ad2e733b9c9bb7992600d809b3ec261');
    $instance->fetch();

    $expects = array(
      'dir2', 'file_1', 'file_2',
    );

    $results = array();
    foreach ($instance as $content)
    {
      $results[] = $content->getName();
    }

    sort($results);

    $this->assertEquals($results, $expects);
  }
}
