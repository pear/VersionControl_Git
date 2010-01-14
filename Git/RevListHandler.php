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
class VersionControl_Git_RevListHandler
{
    const DEFAULT_TARGET = 'master';

    /**
     * An instance of the VersionControl_Git
     *
     * @var string
     */
    protected $git;

    /**
     * The target for the commit (commit range string, branch name, etc...)
     *
     * @var string
     */
     protected $target;

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

    public function __construct(VersionControl_Git $git)
    {
        $this->git = $git;
        $this->target = self::DEFAULT_TARGET;
    }

    public function target($target)
    {
        $this->target = $target;

        return $this;
    }

    public function maxCount($count)
    {
        $this->commandOptions['max-count'] = $count;

        return $this;
    }

    public function skip($number)
    {
        $this->commandOptions['skip'] = $number;

        return $this;
    }

    public function maxAge($age)
    {
        $this->commandOptions['max-age'] = $age;

        return $this;
    }

    public function minAge($age)
    {
        $this->commandOptions['mix-age'] = $age;

        return $this;
    }

    public function merges()
    {
        $this->commandOptions['merges'] = true;

        return $this;
    }

    public function noMerges()
    {
        $this->commandOptions['merges'] = false;

        return $this;
    }

    public function all()
    {
        $this->commandOptions['all'] = true;

        return $this;
    }

    public function branches()
    {
        $this->commandOptions['branches'] = true;

        return $this;
    }

    public function tags()
    {
        $this->commandOptions['tags'] = true;

        return $this;
    }

    public function remotes()
    {
        $this->commandOptions['remotes'] = true;

        return $this;
    }

    public function getOption($key)
    {
        if (isset($this->commandOptions[$key])) {
            return $this->commandOptions[$key];
        }

        return false;
    }

    public function reset()
    {
        foreach ($this->commandOptions as $k => $v) {
            $this->commandOptions[$k] = null;
        }

        $this->target = self::DEFAULT_TARGET;

        return $this;
    }

    public function execute()
    {
        $string = $this->git->executeGit('rev-list '.escapeshellcmd($this->target).$this->buildOptionsString($this->commandOptions).' --pretty=raw');
        $lines = explode("\n", $string);

        $this->reset();

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

            $commits[] = VersionControl_Git_Commit::createInstanceByArray(array(
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

    protected function buildOptionsString($options)
    {
        $result = '';

        foreach ($options as $k => $v)
        {
            if (null === $v) {
                continue;
            }

            if (true === $k) {
                $result .= ' --'.$k;
            } elseif (false === $k) {
                $result .= ' --no-'.$k;
            } else {
                $result .= ' --'.$k.'='.escapeshellarg($v);
            }
        }

        return $result;
    }
}
