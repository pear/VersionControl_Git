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
 * The class represents Git commits
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git_Commit extends VersionControl_Git_Component
{
    /**
     * The identifier of this commit
     *
     * @var string
     */
    public $commit;

    /**
     * The identifier of this tree
     *
     * @var string
     */
    public $tree;

    /**
     * The identifier of this parent
     *
     * @var string
     */
    public $parent;

    /**
     * The identifier of this author
     *
     * @var string
     */
    public $author;

    /**
     * The identifier of this commiter
     *
     * @var string
     */
    public $commiter;

    /**
     * The identifier of this message
     *
     * @var string
     */
    public $message;

    /**
     * The identifier of this message
     *
     * @var string
     */
    public $createdAt;

    /**
     * The identifier of this message
     *
     * @var string
     */
    public $commitedAt;

    public static function createInstanceByArray($git, $array)
    {
        $obj = new VersionControl_Git_Commit($git);

        foreach ($array as $k => $v) {
          $method = 'set'.ucfirst($k);

            if (is_callable(array($obj, $method))) {
                $obj->$method($v);
            }
        }

        return $obj;
    }

    public function setCommit($commit)
    {
      $parts = explode(' ', $commit, 2);

      if (2 != count($parts) || 'commit' !== $parts[0]) {
          return false;
      }

      $this->commit = $parts[1];
    }

    public function setTree($tree)
    {
      $parts = explode(' ', $tree, 2);

      if (2 != count($parts) || 'tree' !== $parts[0]) {
          return false;
      }

      $this->tree = $parts[1];
    }

    public function setParents($parent)
    {
      $parent = (array)$parent;

      $parts = explode(' ', array_shift($parent), 2);

      if (2 != count($parts) || 'parent' !== $parts[0]) {
          return false;
      }

      $this->parent = $parts[1];
    }

    public function hasParents()
    {
      return (bool)($this->parent);
    }

    public function getParents()
    {
      if (!$this->hasParents())
      {
        return false;
      }

      $revlist = $this->git->getRevListHandler()
        ->target($this->parent)
        ->maxCount(1)
        ->execute();

      if (!$revlist)
      {
        return false;
      }

      return $revlist[1];
    }

    public function setAuthor($author)
    {
      $parts = explode(' ', $author, 2);

      if (2 != count($parts) || 'author' !== $parts[0]) {
          return false;
      }

      list ($name, $date) = $this->parseUser($parts[1]);
      $this->author = $name;
      $this->createdAt = $date;
    }

    public function setCommiter($commiter)
    {
      $parts = explode(' ', $commiter, 2);

      if (2 != count($parts) || 'commiter' !== $parts[0]) {
          return false;
      }

      list ($name, $date) = $this->parseUser($parts[1]);
      $this->commiter = $name;
      $this->commitedAt = $date;
    }

    public function setMessage($message)
    {
      $this->message = $message;
    }

    protected function parseUser($userAndTimestamp)
    {
      $matches = array();
      if (preg_match('/^(.+) (\d+) .*$/', $userAndTimestamp, $matches)) {
        return array($matches[1], new DateTime('@'.$matches[2]));
      }

      return array(null, null);
    }
}
