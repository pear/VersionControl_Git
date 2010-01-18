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
class VersionControl_Git_Util_Command extends VersionControl_Git_Component
{
  protected $subCommand = '';

  protected $arguments = array();

  protected $options = array();

  protected $doubleDash = false;

  public function setSubCommand($command)
  {
    $this->subCommand = $command;

    return $this;
  }

  public function setOptions($options)
  {
    $this->options = $options;

    return $this;
  }

  public function setArguments($arguments)
  {
    $this->arguments = array_values($arguments);

    return $this;
  }

  public function setOption($name, $value = true)
  {
    $this->options[$name] = $value;

    return $this;
  }

  public function addArgument($value)
  {
    $this->arguments[] = $value;

    return $this;
  }

  public function addDoubleDash($isAdding)
  {
    $this->doubleDash = $isAdding;

    return $this;
  }

  public function execute($arguments = array(), $options = array())
  {
    if (!$this->subCommand) {
      throw new PEAR_Exception('You must specified "subCommand"');
    }

    $command = $this->git->getGitCommandPath().' '.$this->subCommand;

    $arguments = array_merge($this->arguments, $arguments);
    $options = array_merge($this->options, $options);

    foreach ($options as $k => $v) {
      if (false === $v) {
        continue;
      }

      if (1 === strlen($k)) {
        $command .= ' -'.$k;
      } else {
        $command .= ' --'.$k;
      }

      if (true !== $v) {
        $command .= '='.escapeshellarg($v);
      }
    }

    foreach ($arguments as $v) {
      $command .= ' '.escapeshellarg($v);
    }

    if ($this->doubleDash) {
      $command .= ' --';
    }

    $currentDir = getcwd();
    chdir($this->git->getDirectory());

    $outputFile = tempnam(sys_get_temp_dir(), 'VCG');

    $status = trim(shell_exec($command.' > '.$outputFile.'; echo $?'));
    $result = file_get_contents($outputFile);
    unlink($outputFile);

    chdir($currentDir);

    if ($status) {
      throw new PEAR_Exception('Some errors in executing git command: '.$result);
    }

    return $result;
  }
}
