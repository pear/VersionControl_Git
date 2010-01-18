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
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $this->assertTrue($instance instanceof VersionControl_Git);
  }

  public function testGetCommits()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $commits = $instance->getCommits();

    $this->assertTrue($commits[0] instanceof VersionControl_Git_Object_Commit);
    $this->assertEquals(count($commits), 100);

    $commits = $instance->getCommits('master', 5);
    $this->assertEquals(count($commits), 5);

    $commits = $instance->getCommits('master', 1);
    $this->assertEquals($commits[0]->id, 'ddf8aa7e97a206847658c90a26fe740b2e17231a');

    $commits = $instance->getCommits('branch1', 1);
    $this->assertEquals($commits[0]->id, '373efdec06a5847fd279d8c442dbfdd5df41e783');

    $commits = $instance->getCommits('master', 1, 10);
    $this->assertEquals($commits[0]->id, 'b5e1faccd790d04e9ad2d48b26f8f836ece4af5d');
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
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');

    $branches = $instance->getBranches();
    $this->assertEquals(count($branches), 8);
    $this->assertEquals($branches[0], 'branch1');
    $this->assertEquals($branches[7], 'master');
  }

  public function testGetCurrentBranch()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');

    $this->assertEquals($instance->getCurrentBranch(), 'master');

    $instance->checkout('branch1');
    $this->assertEquals($instance->getCurrentBranch(), 'branch1');

    $instance->checkout('master');
  }

  public function testGetHeadCommits()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $heads = $instance->getHeadCommits();

    $this->assertEquals($heads['master'], 'ddf8aa7e97a206847658c90a26fe740b2e17231a');
    $this->assertEquals($heads['branch2'], $heads['master']);
    $this->assertEquals($heads['branch1'], '373efdec06a5847fd279d8c442dbfdd5df41e783');
  }

  public function testGetTags()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $tags = $instance->getTags();

    $this->assertEquals($tags['tag1'], 'ddf8aa7e97a206847658c90a26fe740b2e17231a');
  }

  public function testGetTree()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $tree = $instance->getTree('master')->fetch();

    $this->assertTrue($tree instanceof VersionControl_Git_Object_Tree);
    $this->assertTrue($tree->current() instanceof VersionControl_Git_Object_Blob);
  }

  public function testGetDirectory()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $this->assertEquals($instance->getDirectory(), './fixtures/001_VersionControl_Git');
  }

  public function testGetGitCommandPath()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $this->assertEquals($instance->getGitCommandPath(), '/usr/bin/git');
  }

  public function testSetGitCommandPath()
  {
    $instance = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $this->assertEquals($instance->getGitCommandPath(), '/usr/bin/git');
    $instance->setGitCommandPath('/usr/local/bin/git');
    $this->assertEquals($instance->getGitCommandPath(), '/usr/local/bin/git');
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

