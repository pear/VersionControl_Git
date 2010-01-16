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

    $commits = $instance->getCommits('master', 5);
    $this->assertEquals(count($commits), 5);
  }

  public function testCreateClone()
  {
    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->createClone('git://gist.github.com/265855.git');
    $this->assertTrue(is_dir($dirname.DIRECTORY_SEPARATOR.'265855'));
    $this->removeDirectory($dirname);

    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->createClone('git://gist.github.com/265855.git', true);
    $this->assertTrue(is_file($dirname.DIRECTORY_SEPARATOR.'265855.git'.DIRECTORY_SEPARATOR.'HEAD'));
    $this->removeDirectory($dirname);
  }

  public function testInitialRepository()
  {
    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->initialRepository();
    $this->assertTrue(is_dir($dirname.DIRECTORY_SEPARATOR.'.git'));
    $this->removeDirectory($dirname);

    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->initialRepository(true);
    $this->assertTrue(is_file($dirname.DIRECTORY_SEPARATOR.'HEAD'));
    $this->removeDirectory($dirname);
  }

  public function testGetBranches()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');

    // this test must be added
  }

  public function testGetCurrentBranch()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');

    // this test must be added
  }

  public function testGetHeadCommits()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');
    $instance->getHeadCommits();

    // this test must be added
  }

  public function testGetTags()
  {
    $instance = new VersionControl_Git('/home/co3k/sf/op3-ebihara');
    $instance->getHeadTags();

    // this test must be added
  }

  protected function generateTmpDir()
  {
    $dirname = sys_get_temp_dir().DIRECTORY_SEPARATOR.'VCG_test_'.time();
    mkdir($dirname);

    return $dirname;
  }

  protected function removeDirectory($dir)
  {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST) as $file)
    {
      if ($file->isDir())
      {
        rmdir($file->getPathname());
      }
      else
      {
        unlink($file->getPathname());
      }
    }

    rmdir($dir);
  }
}

