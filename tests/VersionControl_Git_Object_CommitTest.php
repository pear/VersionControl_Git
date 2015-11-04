<?php
require_once 'VersionControl/Git.php';

require_once dirname(__FILE__) . '/checkFixtures.php';

class VersionControl_Git_Object_CommitTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertTrue($instance instanceof VersionControl_Git_Object_Commit);
  }

  public function testCreateInstanceByArrayException()
  {
    $this->setExpectedException('VersionControl_Git_Exception');

    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    VersionControl_Git_Object_Commit::createInstanceByArray($git, array());
  }

  public function testCreateInstanceByArray()
  {
    $this->assertTrue($this->getCreatedInstance() instanceof VersionControl_Git_Object_Commit);
  }

  public function testSetTree()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertFalse($instance->setTree('cca66138995a95b45a725e8727ee97a20a816d41'));
    $this->assertNull($instance->setTree('tree cca66138995a95b45a725e8727ee97a20a816d41'));
  }

  public function testGetTree()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertNull($instance->getTree());

    $instance->setTree('tree cca66138995a95b45a725e8727ee97a20a816d41');
    $this->assertEquals($instance->getTree(), 'cca66138995a95b45a725e8727ee97a20a816d41');
  }

  public function testSetParents()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertFalse($instance->setParents('ddf8aa7e97a206847658c90a26fe740b2e17231a'));
    $this->assertNull($instance->setParents('parent ddf8aa7e97a206847658c90a26fe740b2e17231a'));
    $this->assertNull($instance->setParents(array('parent ddf8aa7e97a206847658c90a26fe740b2e17231a', 'parent ddf8aa7e97a206847658c90a26fe740b2e17231a')));
  }

  public function testHasParents()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertFalse($instance->hasParents());
    $instance->setParents(array('parent ddf8aa7e97a206847658c90a26fe740b2e17231a', 'parent ddf8aa7e97a206847658c90a26fe740b2e17231a'));
    $this->assertTrue($instance->hasParents());
  }

  public function testGetParents()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertFalse($instance->getParents());

    $instance->setParents(array('parent ddf8aa7e97a206847658c90a26fe740b2e17231a', 'invalid'));
    $this->assertFalse($instance->getParents());

    $instance->setParents(array('parent ddf8aa7e97a206847658c90a26fe740b2e17231a', 'parent ddf8aa7e97a206847658c90a26fe740b2e17231a'));
    $parents = $instance->getParents();
    $this->assertEquals((string)$parents[0], 'ddf8aa7e97a206847658c90a26fe740b2e17231a');
    $this->assertEquals((string)$parents[1], 'ddf8aa7e97a206847658c90a26fe740b2e17231a');
    $this->assertEquals(count($parents), 2);
  }

  public function testSetAuthor()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertFalse($instance->setAuthor('Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900'));
    $this->assertNull($instance->setAuthor('author Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900'));
  }

  public function testGetAuthor()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');
    $instance->setAuthor('author Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900');

    $this->assertEquals($instance->getAuthor(), 'Kousuke Ebihara <ebihara@tejimaya.com>');

    $instance->setAuthor('author Kousuke Ebihara <ebihara@tejimaya.com>');
    $this->assertNull($instance->getAuthor());
  }

  public function testGetCreatedAt()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');
    $instance->setAuthor('author Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900');

    $this->assertEquals(date('YmdHis', $instance->getCreatedAt()), '20100121011001');

    $instance->setAuthor('author Kousuke Ebihara <ebihara@tejimaya.com>');
    $this->assertNull($instance->getCreatedAt());
  }

  public function testSetCommitter()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $this->assertFalse($instance->setCommitter('Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900'));
    $this->assertNull($instance->setCommitter('committer Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900'));
  }

  public function testGetCommitter()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');
    $instance->setCommitter('committer Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900');

    $this->assertEquals($instance->getCommitter(), 'Kousuke Ebihara <ebihara@tejimaya.com>');

    $instance->setCommitter('committer Kousuke Ebihara <ebihara@tejimaya.com>');
    $this->assertNull($instance->getCommitter());
  }

  public function testGetCommittedAt()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');
    $instance->setCommitter('committer Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900');

    $this->assertEquals(date('YmdHis', $instance->getCommittedAt()), '20100121011001');

    $instance->setCommitter('committer Kousuke Ebihara <ebihara@tejimaya.com>');
    $this->assertNull($instance->getCommittedAt());
  }

  public function testSetMessage()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');

    $instance->setMessage('message');
    $this->assertEquals($instance->getMessage(), 'message');
  }

  public function testFetchException()
  {
    $this->setExpectedException('VersionControl_Git_Exception');

    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, 'invalid');
    $instance->fetch();
  }

  public function testFetch()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = new VersionControl_Git_Object_Commit($git, '4ed54abb8efca38a0c794ca414b1f296279e0d85');
    $instance->fetch();

    $this->assertEquals($instance->getTree(), 'cca66138995a95b45a725e8727ee97a20a816d41');
    $this->assertFalse($instance->hasParents());
    $this->assertEquals($instance->getAuthor(), 'Kousuke Ebihara <ebihara@tejimaya.com>');
    $this->assertEquals(date('YmdHis', $instance->getCreatedAt()), '20100121011001');
    $this->assertEquals($instance->getCommitter(), 'Kousuke Ebihara <ebihara@tejimaya.com>');
    $this->assertEquals(date('YmdHis', $instance->getCommittedAt()), '20100121011001');
    $this->assertEquals($instance->getMessage(), 'added directories and files');
  }

  protected function getCreatedInstance()
  {
    $git = new VersionControl_Git(dirname(__FILE__) . '/fixtures/001_VersionControl_Git');
    $instance = VersionControl_Git_Object_Commit::createInstanceByArray($git, array(
      'commit'    => 'commit 4ed54abb8efca38a0c794ca414b1f296279e0d85',
      'tree'      => 'tree cca66138995a95b45a725e8727ee97a20a816d41',
      'parent'    => 'parent ddf8aa7e97a206847658c90a26fe740b2e17231a',
      'author'    => 'author Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900',
      'committer' => 'committer Kousuke Ebihara <ebihara@tejimaya.com> 1264003801 +0900',
    ));

    return $instance;
  }
}
