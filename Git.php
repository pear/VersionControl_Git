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
     * Array of options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param string $reposDir  A directory path to a git repository
     */
    public function __construct($reposDir = './', array $options = array())
    {
        if (!is_dir($reposDir)) {
            throw new PEAR_Exception('You must specified readable directory as repository.');
        }

        $this->directory = $reposDir;
        $this->options = $options;
    }

    // parsing will be VersionControl_Git_Commit::fetchCollection
    //   コミットの要素は VersionControl_Git_Commit のインスタンスとなり、
    //      commit プロパティ: VersionControl_Git_Commit を特定するID
    //      tree             : ?
    //      parents          : ?
    //      author           : VersionControl_Git_Author のインスタンス
    //      commiter         : VersionControl_Git_Author のインスタンス
    //      message          : 文字列
    public function getCommits($maxResults = 100)
    {
      $string = $this->executeGit('log -'.escapeshellcmd($maxResults).' --pretty=raw');
      $lines = explode("\n", $string);

      $commits = array();

      while (count($lines)) {
          $commit = array_shift($lines);
          $tree = array_shift($lines);

          $parents = array();
          while (count($lines) && 0 === strpos($lines[0], 'parent')) {
              $parents[] = array_shift($lines);
          }

          $author = array_shift($lines);
          $commiter = array_shift($lines);

          $message = array();
          array_shift($lines);
          while (count($lines) && 0 === strpos($lines[0], '   ')) {
              $message[] = trim(array_shift($lines));
          }
          array_shift($lines);

          $commits[] = array(
            'commit' => $commit,
            'tree' => $tree,
            'parents' => $parents,
            'author' => $author,
            'commiter' => $commiter,
            'message' => implode("\n", $message),
          );
      }

      return $commits;
    }

    protected function executeGit($subCommand)
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

    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }
}
