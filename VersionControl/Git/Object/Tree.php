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

/**
 * The OO interface for tree object
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <ebihara@php.net>
 * @copyright 2010 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git_Object_Tree extends VersionControl_Git_Object implements SeekableIterator
{
    /**
     * The current position
     *
     * @var int
     */
    protected $position = 0;

    /**
     * An array of instances of object
     *
     * @var array
     */
    protected $objects = array();

    /**
     * Constructor
     *
     * @param VersionControl_Git $git An instance of the VersionControl_Git
     * @param string             $id  An identifier of this object
     * @param string             $name  A human-readable name of this object
     */
    public function __construct(VersionControl_Git $git, $id, $name = null)
    {
        $this->position = 0;

        parent::__construct($git, $id, $name);
    }

    /**
     * Fetch the substance of this object
     *
     * @return VersionControl_Git_Object The "$this" object for method chain
     */
    public function fetch()
    {
        $command = $this->git->getCommand('ls-tree')
            ->addArgument('-z')->addArgument($this->id);

        $lines = explode("\0", trim($command->execute()));
        foreach ($lines as $line) {
            $itemString = str_replace("\t", ' ', $line);

            list ($mode, $type, $id, $name) = explode(' ', $itemString, 4);

            $class = 'VersionControl_Git_Object_'.ucfirst($type);

            $this->objects[] = new $class($this->git, $id, $name);
        }

        return $this;
    }

	/**
	 * Seeks to the specified position
	 *
	 * @param int $position The position to seek to
	 *
	 * @return void
	 * @throws VersionControl_Git_Exception
	 */
    public function seek($position): void
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new VersionControl_Git_Exception('Invalid offset is specified');
        }
    }

    /**
     * Rewind this iterator to the first position
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Get the current value
     *
     * @return VersionControl_Git_Object
     */
	#[ReturnTypeWillChange]
    public function current()
    {
        return $this->objects[$this->position];
    }

    /**
     * Get the current key
     *
     * @return int
     */
	#[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Move forward to next positon
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->objects[$this->position]);
    }
}
