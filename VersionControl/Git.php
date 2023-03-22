<?php

/**
 * Copyright 2010 Kousuke Ebihara
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
 * PHP Version 5
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <ebihara@php.net>
 * @copyright 2010 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

require_once 'System.php';

require_once 'PEAR/Exception.php';
require_once 'VersionControl/Git/Exception.php';

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
 * @author    Kousuke Ebihara <ebihara@php.net>
 * @copyright 2010 Kousuke Ebihara
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
    protected $gitCommandPath;

    /**
     * Constructor
     *
     * @param string $reposDir A directory path to a git repository
     */
    public function __construct($reposDir = './')
    {
        if (!is_dir($reposDir)) {
            $message = 'You must specified readable directory as repository.';
            throw new VersionControl_Git_Exception($message);
        }

        $this->directory = $reposDir;
    }

    /**
     * Get Git version (e.g. 1.7.0)
     *
     * @return string
     */
    public function getGitVersion()
    {
        $command = $this->getCommand('--version');

        return substr(trim($command->execute()), strlen('git version '));
    }

    /**
     * Get an instance of the VersionControl_Git_Util_RevListFetcher that is
     * related to this repository
     *
     * @return VersionControl_Git_Util_RevListFetcher
     */
    public function getRevListFetcher()
    {
        return new VersionControl_Git_Util_RevListFetcher($this);
    }

    /**
     * Get an array of the VersionControl_Git_Object_Commit objects
     *
     * @param mixed $object     The commit object. It can be string
     *                          or an instance of the VersionControl_Git_Object
     * @param int   $maxResults A number of results
     * @param int   $offset     A starting position of results
     *
     * @return array
     */
    public function getCommits($object = 'master', $maxResults = 100, $offset = 0)
    {
        return $this->getRevListFetcher()
            ->target((string)$object)
            ->addDoubleDash(true)
            ->setOption('max-count', $maxResults)
            ->setOption('skip', $offset)
            ->fetch();
    }

    /**
     * Create a new clone from the specified repository
     *
     * It is wrapper of "git clone" command.
     *
     * @param string $repository The path to repository
     * @param bool   $isBare     Whether to create bare clone
     * @param string $directory  The path to new repository
     *
     * @return null
     */
    public function createClone($repository, $isBare = false, $directory = null)
    {
        $command = $this->getCommand('clone')
            ->setOption('bare', $isBare)
            ->setOption('q')
            ->addArgument($repository);

        if (null !== $directory) {
            $command->addArgument($directory);

            // cloning to empty directory is supported in 1.6.2-rc0 +
            // see: http://git.kernel.org/?p=git/git.git;a=commit;h=55892d239819
            if (is_dir($directory) && version_compare('1.6.1.4', $this->getGitVersion(), '>=')) {
                $isEmptyDir = true;
                $entries = scandir($directory);
                foreach ($entries as $entry) {
                    if ('.' !== $entry && '..' !== $entry) {
                        $isEmptyDir = false;

                        break;
                    }
                }

                if ($isEmptyDir) {
                    @rmdir($directory);
                }
            }
        }
        $command->execute();

        $this->directory = $directory;
    }

    /**
     * Initialize a new repository
     *
     * It is wrapper of "git init" command.
     *
     * @param bool $isBare Whether to create bare clone
     *
     * @return null
     */
    public function initRepository($isBare = false)
    {
        if (!$isBare || version_compare('1.5.6.6', $this->getGitVersion(), '<='))
        {
            $this->getCommand('init')
                ->setOption('bare', $isBare)
                ->setOption('q')
                ->execute();
        }
        else
        {
            // see: http://git.kernel.org/?p=git/git.git;a=commit;h=74d3b23
            $this->getCommand('--bare')
                ->addArgument('init')
                ->addArgument('-q')  // it is just a quick hack
                ->execute();
        }
    }

    /**
     * Alias of VersionControl_Git::initRepository()
     *
     * This method is available for backward compatibility.
     *
     * @param bool $isBare Whether to create bare clone
     *
     * @return null
     */
    public function initialRepository($isBare = false)
    {
        $this->initRepository($isBare);
    }

    /**
     * Get an array of branch names
     *
     * @return array
     */
    public function getBranches()
    {
        $result = array();

        $commandResult = explode(PHP_EOL,
            rtrim($this->getCommand('branch')->execute()));
        foreach ($commandResult as $k => $v) {
            $result[$k] = substr($v, 2);
        }

        return $result;
    }

    /**
     * Get an array of remote branch names
     *
     * @param string $name The name of remote repository
     *
     * @return array
     */
    public function getRemoteBranches($name = 'origin')
    {
        $result = array();

        $commandResult = $this->getCommand('branch')
            ->setOption('r')
            ->execute();
        $commandResult = explode(PHP_EOL, rtrim($commandResult));

        foreach ($commandResult as $v) {
            $v = trim($v);

            $prefix = $name.'/';
            if (0 !== strpos($v, $prefix)) {
                continue;
            }

            $result[] = substr($v, strlen($prefix));
        }

        return $result;
    }

    /**
     * Get a current branch name
     *
     * @return string
     */
    public function getCurrentBranch()
    {
        $commandResult = $this->getCommand('symbolic-ref')
            ->addArgument('HEAD')
            ->execute();

        return substr(trim($commandResult), strlen('refs/heads/'));
    }

    /**
     * Checkout the specified branch
     *
     * Checking out a path is not supported currently.
     *
     * @param mixed $object The commit object. It can be string
     *                      or an instance of the VersionControl_Git_Object
     *
     * @return null
     */
    public function checkout($object)
    {
        $this->getCommand('checkout')
            ->addDoubleDash(true)
            ->setOption('q')
            ->addArgument((string)$object)
            ->execute();
    }

    /**
     * Get an array of branch object names
     *
     * @return array
     */
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

    /**
     * Get an array of tag names
     *
     * @return array
     */
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

    /**
     * Get an instance of the VersionControl_Git_Object_Tree that is related
     * to this repository
     *
     * @param mixed $object The commit object. It can be string
     *                      or an instance of the VersionControl_Git_Object
     *
     * @return VersionControl_Git_Object_Tree
     */
    public function getTree($object)
    {
        return new VersionControl_Git_Object_Tree($this, (string)$object);
    }

    /**
     * Get an instance of the VersionControl_Git_Util_Command that is related
     * to this repository
     *
     * @param string $subCommand A subcommand to execute
     *
     * @return VersionControl_Git_Util_Command
     */
    public function getCommand($subCommand)
    {
        $command = new VersionControl_Git_Util_Command($this);
        $command->setSubCommand($subCommand);

        return $command;
    }

    /**
     * Get the directory for this repository
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get the location to git binary
     *
     * @return string
     */
    public function getGitCommandPath()
    {
        // Guess path to git binary
        if (!$this->gitCommandPath) {
            $this->gitCommandPath = @System::which('git');

            if (!$this->gitCommandPath) {
                $message = 'Guessing path to git binary is failed.'
                         . ' You must specify path to git binary yourself.';
                throw new VersionControl_Git_Exception($message);
            }
        }

        return $this->gitCommandPath;
    }

    /**
     * Set the location to git binary
     *
     * @param string $path The location to git binary
     *
     * @return null
     */
    public function setGitCommandPath($path)
    {
        $this->gitCommandPath = $path;
    }
}
