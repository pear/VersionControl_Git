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

require_once 'VersionControl/Git/Util/Command.php';
require_once 'VersionControl/Git/Util/RevListFetcher.php';

require_once 'VersionControl/Git/Object.php';
require_once 'VersionControl/Git/Object/Commit.php';
require_once 'VersionControl/Git/Object/Blob.php';
require_once 'VersionControl/Git/Object/Tree.php';

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
    protected $gitCommandPath = '/usr/bin/git';

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

    /**
     * Get an instance of the VersionControl_Git_Util_RevListFetcher that belongs this repository
     *
     * @return VersionControl_Git_Util_RevListFetcher
     */
    public function getRevListFetcher()
    {
        return new VersionControl_Git_Util_RevListFetcher($this);
    }

    public function getCommits($object = 'master', $maxResults = 100, $offset = 0)
    {
        return $this->getRevListFetcher()
            ->target($object)
            ->addDoubleDash(true)
            ->setOption('max-count', $maxResults)
            ->setOption('skip', $offset)
            ->fetch();
    }

    public function createClone($repository, $isBare = false)
    {
      $this->getCommand('clone')
        ->setOption('bare', $isBare)
        ->setOption('q')
        ->addArgument($repository)
        ->execute();
    }

    public function initialRepository($isBare = false)
    {
      $this->getCommand('init')
        ->setOption('bare', $isBare)
        ->setOption('q')
        ->execute();
    }

    public function getBranches()
    {
      $result = array();

      $commandResult = explode(PHP_EOL, rtrim($this->getCommand('branch')->execute()));
      foreach ($commandResult as $k => $v) {
        $result[$k] = substr($v, 2);
      }

      return $result;
    }

    public function getCurrentBranch()
    {
      return substr(trim($this->getCommand('symbolic-ref')->addArgument('HEAD')->execute()), strlen('refs/heads/'));
    }

    public function checkout($object)
    {
      $this->getCommand('checkout')
        ->addDoubleDash(true)
        ->setOption('q')
        ->addArgument($object)
        ->execute();
    }

    public function getHeadCommits()
    {
      $result = array();
      $command = $this->getCommand('for-each-ref')
        ->setOption('format', '%(refname),%(objectname)')
        ->addArgument('refs/heads');

      $commandResult = explode(PHP_EOL, trim($command->execute()));
      foreach ($commandResult as $v) {
        $pieces = explode(',', $v);
        if (2 == count($pieces)) {
          $result[substr($pieces[0], strlen('refs/heads/'))] = $pieces[1];
        }
      }

      return $result;
    }

    public function getTags()
    {
      $result = array();

      $command = $this->getCommand('for-each-ref')
        ->setOption('format', '%(refname),%(objectname)')
        ->addArgument('refs/tags');

      $commandResult = explode(PHP_EOL, trim($command->execute()));
      foreach ($commandResult as $v) {
        $pieces = explode(',', $v);
        if (2 == count($pieces)) {
          $result[substr($pieces[0], strlen('refs/tags/'))] = $pieces[1];
        }
      }

      return $result;
    }

    public function getTree($object)
    {
      return new VersionControl_Git_Object_Tree($this, $object);
    }

    public function getCommand($subCommand)
    {
      $command = new VersionControl_Git_Util_Command($this);
      $command->setSubCommand($subCommand);

      return $command;
    }

    public function getDirectory()
    {
      return $this->directory;
    }

    public function getGitCommandPath()
    {
      return $this->gitCommandPath;
    }

    public function setGitCommandPath($path)
    {
      $this->gitCommandPath = $path;
    }
}
