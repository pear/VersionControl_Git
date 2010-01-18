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

/**
 * The class represents Git rev-list
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git_Util_RevListFetcher extends VersionControl_Git_Util_Command
{
    const DEFAULT_TARGET = 'master';

    /**
     * The target for the commit (commit range string, branch name, etc...)
     *
     * @var string
     */
     protected $target = self::DEFAULT_TARGET;

    /**
     * An command options
     *
     * @var array
     */
    protected $commandOptions = array(
        'max-count' => null,
        'skip'      => null,
        'max-age'   => null,
        'min-age'   => null,
        'merges'    => null,
        'all'       => null,
        'branches'  => null,
        'tags'      => null,
        'remotes'   => null,
    );

    public function target($target)
    {
        $this->target = $target;

        return $this;
    }

    public function reset()
    {
        $this->options = array();
        $this->target = self::DEFAULT_TARGET;

        return $this;
    }

    public function fetch()
    {
        $string = $this->setSubCommand('rev-list')
          ->setOption('pretty', 'raw')
          ->setArguments(array($this->target))
          ->execute();

        $lines = explode("\n", $string);

        $this->reset();

        $commits = array();

        while (count($lines)) {
            $commit = array_shift($lines);
            if (!$commit) {
              continue;
            }

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

            $commits[] = VersionControl_Git_Object_Commit::createInstanceByArray($this->git, array(
                'commit' => $commit,
                'tree' => $tree,
                'parents' => $parents,
                'author' => $author,
                'commiter' => $commiter,
                'message' => implode("\n", $message),
            ));
        }

        return $commits;
    }
}
