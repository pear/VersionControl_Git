<?php

/**
 * Copyright 2009 Kousuke Ebihara
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

require_once 'PEAR/Exception.php';
require_once 'VersionControl/Git/Component.php';
require_once 'VersionControl/Git/Commit.php';
require_once 'VersionControl/Git/RevListHandler.php';
require_once 'VersionControl/Git/EntryInterface.php';
require_once 'VersionControl/Git/Blob.php';
require_once 'VersionControl/Git/Tree.php';

/**
 * The OO interface for Git
 *
 * An instance of this class can be handled as OO interface for a Git repository.
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git
{
    /**
     * The directory for this repository
     *
     * @var string
     */
    protected $directory;

    /**
     * Location to git binary
     *
     * @var string
     */
    protected $gitCommand = '/usr/bin/git';

    /**
     * Constructor
     *
     * @param string $reposDir  A directory path to a git repository
     */
    public function __construct($reposDir = './')
    {
        if (!is_dir($reposDir)) {
            throw new PEAR_Exception('You must specified readable directory as repository.');
        }

        $this->directory = $reposDir;
    }

    public function getRevListHandler()
    {
        return new VersionControl_Git_RevListHandler($this);
    }

    public function getCommits($name = 'master', $maxResults = 100, $offset = 0)
    {
        return $this->getRevListHandler()
            ->target($name)
            ->maxCount($maxResults - 1)
            ->skip($offset)
            ->execute();
    }

    public function createClone($repository, $isBare = false)
    {
      $command = 'clone';
      if ($isBare) {
        $command .= ' --bare';
      }
      $command .= ' -q '.escapeshellarg($repository);

      $this->executeGit($command);
    }

    public function initialRepository($isBare = false)
    {
      $command = 'init -q';
      if ($isBare) {
        $command .= ' --bare';
      }

      $this->executeGit($command);
    }

    public function getBranches()
    {
      $result = array();

      $commandResult = explode(PHP_EOL, rtrim($this->executeGit('branch')));
      foreach ($commandResult as $k => $v) {
        $result[$k] = substr($v, 2);
      }

      return $result;
    }

    public function getCurrentBranch()
    {
      return substr(trim($this->executeGit('symbolic-ref HEAD')), strlen('refs/heads/'));
    }

    public function getHeadCommits()
    {
      $result = array();

      $commandResult = explode(PHP_EOL, trim($this->executeGit('for-each-ref '.escapeshellarg('refs/heads').' --format='.escapeshellarg('%(refname),%(objectname)'))));
      foreach ($commandResult as $v) {
        $pieces = explode(',', $v);
        if (2 == count($pieces)) {
          $result[substr($pieces[0], strlen('refs/heads/'))] = $pieces[1];
        }
      }

      return $result;
    }

    public function getHeadTags()
    {
      $result = array();

      $commandResult = explode(PHP_EOL, trim($this->executeGit('for-each-ref '.escapeshellarg('refs/tags').' --format='.escapeshellarg('%(refname),%(objectname)'))));
      foreach ($commandResult as $v) {
        $pieces = explode(',', $v);
        if (2 == count($pieces)) {
          $result[substr($pieces[0], strlen('refs/tags/'))] = $pieces[1];
        }
      }

      return $result;
    }

    public function getTree($commit)
    {
      return new VersionControl_Git_Tree($this, $commit);
    }

    public function executeGit($subCommand)
    {
      $currentDir = getcwd();
      chdir($this->directory);

      $outputFile = tempnam(sys_get_temp_dir(), 'VCG');

      $status = trim(shell_exec($this->gitCommand.' '.$subCommand.' > '.$outputFile.'; echo $?'));
      $result = file_get_contents($outputFile);
      unlink($outputFile);

      chdir($currentDir);

      if ($status)
      {
        throw new PEAR_Exception('Some errors in executing git command: '.$result);
      }

      return $result;
    }
}
