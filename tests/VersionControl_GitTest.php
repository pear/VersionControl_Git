<?php
require_once 'VersionControl/Git.php';

require_once dirname(__FILE__) . '/checkFixtures.php';

class VersionControl_GitTest extends PHPUnit_Framework_TestCase
{
  public function testConstructException()
  {
    $this->setExpectedException('VersionControl_Git_Exception');

    new VersionControl_Git('!This is not valid direcotry!');
  }

  public function testConstruct()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $this->assertTrue($instance instanceof VersionControl_Git);
  }

  public function testGetGitVersion()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $version = $instance->getGitVersion();

    $this->assertTrue((bool)preg_match('/^[0-9]+\.[0-9]+\.[0-9]+/', $version));
  }

  public function testGetCommits()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $commits = $instance->getCommits();

    $this->assertTrue($commits[0] instanceof VersionControl_Git_Object_Commit);
    $this->assertEquals(count($commits), 100);

    $commits = $instance->getCommits('master', 5);
    $this->assertEquals(count($commits), 5);

    $commits = $instance->getCommits('master', 1);
    $this->assertEquals($commits[0]->id, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $commits = $instance->getCommits('branch1', 1);
    $this->assertEquals($commits[0]->id, '373efdec06a5847fd279d8c442dbfdd5df41e783');

    $commits = $instance->getCommits('master', 1, 10);
    $this->assertEquals($commits[0]->id, 'bf3488d82c09a749cefbb2633f9605b6ab5cf71e');
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
    if (version_compare('1.6.0-rc1', $instance->getGitVersion(), '>='))
    {
      // see: http://git.kernel.org/?p=git/git.git;a=commit;h=6612f87
      $this->assertTrue(is_file($dirname.DIRECTORY_SEPARATOR.'265855'.DIRECTORY_SEPARATOR.'HEAD'));
    }
    else
    {
      $this->assertTrue(is_file($dirname.DIRECTORY_SEPARATOR.'265855.git'.DIRECTORY_SEPARATOR.'HEAD'));
    }
    $this->removeDirectory($dirname);

    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->createClone('git://gist.github.com/265855.git', true, $dirname.DIRECTORY_SEPARATOR.'MY_WORKING_COPY');
    $this->assertTrue(is_dir($dirname.DIRECTORY_SEPARATOR.'MY_WORKING_COPY'));
    $this->assertTrue(realpath($instance->getDirectory()) === realpath($dirname.DIRECTORY_SEPARATOR.'MY_WORKING_COPY'));
    $this->removeDirectory($dirname);

    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->createClone('git://gist.github.com/265855.git', false, $dirname);
    $this->assertTrue(is_file($dirname.DIRECTORY_SEPARATOR.'patch_for_pecl_runkit.diff'));
    $this->removeDirectory($dirname);
  }

  public function testInitRepository()
  {
    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->initRepository();
    $this->assertTrue(is_dir($dirname.DIRECTORY_SEPARATOR.'.git'));
    $this->removeDirectory($dirname);

    $dirname = $this->generateTmpDir();
    $instance = new VersionControl_Git($dirname);
    $instance->initRepository(true);
    $this->assertTrue(is_file($dirname.DIRECTORY_SEPARATOR.'HEAD'));
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
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');

    $branches = $instance->getBranches();
    $this->assertEquals(count($branches), 8);
    $this->assertEquals($branches[0], 'branch1');
    $this->assertEquals($branches[7], 'master');
  }

  public function testGetRemoteBranches()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');

    $branches = $instance->getRemoteBranches();

    $this->assertEquals(count($branches), 8);
    $this->assertEquals($branches[0], 'branch1');
    $this->assertEquals($branches[7], 'master');

    $this->assertEquals(count($instance->getRemoteBranches('undefined-repository')), 0);
  }

  public function testGetCurrentBranch()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');

    $this->assertEquals($instance->getCurrentBranch(), 'master');

    $instance->checkout('branch1');
    $this->assertEquals($instance->getCurrentBranch(), 'branch1');

    $instance->checkout('master');
  }

  public function testGetHeadCommits()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $heads = $instance->getHeadCommits();

    $this->assertEquals($heads['master'], '4ed54abb8efca38a0c794ca414b1f296279e0d85');
    $this->assertEquals($heads['branch1'], '373efdec06a5847fd279d8c442dbfdd5df41e783');
  }

  public function testGetTags()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $tags = $instance->getTags();

    $this->assertEquals($tags['tag1'], 'ddf8aa7e97a206847658c90a26fe740b2e17231a');
  }

  public function testGetTree()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $tree = $instance->getTree('master')->fetch();

    $this->assertTrue($tree instanceof VersionControl_Git_Object_Tree);
    $this->assertTrue($tree->current() instanceof VersionControl_Git_Object_Blob);
  }

  public function testGetDirectory()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $this->assertEquals($instance->getDirectory(), dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
  }

  public function testGetGitCommandPath()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $this->assertEquals($instance->getGitCommandPath(), @System::which('git'));
  }

  public function testSetGitCommandPath()
  {
    $instance = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $this->assertEquals($instance->getGitCommandPath(), @System::which('git'));
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
    system('rm -rf '.$dir);
  }
}

