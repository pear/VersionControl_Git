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
class VersionControl_Git_Tree extends VersionControl_Git_Entry implements SeekableIterator
{
  protected $position = 0;

  protected $entries = array();

  public function __construct(VersionControl_Git $git, $commit, $hash = null, $type = null, $name = null)
  {
    $this->position = 0;

    parent::__construct($git, $commit, $hash, $type, $name);

//    $this->parseTree($commit);
  }

  public function fetch()
  {
    $lines = explode(PHP_EOL, trim($this->git->executeGit('ls-tree '.escapeshellarg($this->hash))));
    foreach ($lines as $line)
    {
      list ($mode, $type, $hash, $name) = explode(' ', str_replace("\t", ' ', $line), 4);

      $class = 'VersionControl_Git_'.ucfirst($type);
      $this->entries[] = new $class($this->git, $hash, $type, $name);
    }

    return $this;
  }

  public function seek($position)
  {
    $this->position = $position;

    if (!$this->valid()) {
      throw new OutOfBoundsException('Invalid');
    }
  }

  public function rewind()
  {
      $this->position = 0;
  }

  public function current()
  {
    return $this->entries[$this->position];
  }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->entries[$this->position]);
    }

  public function isBlob()
  {
    return false;
  }

  public function isTree()
  {
    return true;
  }
}
