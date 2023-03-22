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
 * The OO interface for executing Git command
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <ebihara@php.net>
 * @copyright 2010 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git_Util_Command extends VersionControl_Git_Component
{
    /**
     * The subcommand name
     *
     * @var string
     */
    protected $subCommand = '';

    /**
     * An array of arguments
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * An array of options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Key-value array of environment variables
     *
     * @var array
     */
    protected $envVars = array();

    /**
     * Flag to add "--" before the end of command
     *
     * If this is true, command is executed with "--".
     * It is need by some Git command for understanding the specified
     * object is not a path.
     *
     * @var bool
     */
    protected $doubleDash = false;

    /**
     * Set the subcommand name
     *
     * @param string $command The subcommand name
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function setSubCommand($command)
    {
        $this->subCommand = $command;

        return $this;
    }

    /**
     * Set the options
     *
     * @param array $options An array of new options
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set the arguments
     *
     * @param array $arguments An array of new arguments
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function setArguments($arguments)
    {
        $this->arguments = array_values($arguments);

        return $this;
    }

    /**
     * Set the environment variables to pass to the process.
     *
     * @param array $envs Array of new environment variables
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function setEnvVars($envVars)
    {
        $this->envVars = $envVars;

        return $this;
    }

    /**
     * Set a option
     *
     * @param string      $name  A name of option
     * @param string|bool $value A value of option. If it is "true", this option
     *                           doesn't have a value. If it is "false", this option
     *                           will be not used
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function setOption($name, $value = true)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set a single environment variable
     *
     * @param string      $name  A name of environment variable
     * @param string|bool $value A value of environment variable
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function setEnvVar($name, $value)
    {
        $this->envVars[$name] = $value;

        return $this;
    }

    /**
     * Add an argument
     *
     * @param string $value A value of argument
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function addArgument($value)
    {
        $this->arguments[] = $value;

        return $this;
    }

    /**
     * Add / Remove a double-dash
     *
     * @param string $isAdding Whether to add double-dash
     *
     * @return VersionControl_Git_Util_Command The "$this" object for method chain
     */
    public function addDoubleDash($isAdding)
    {
        $this->doubleDash = $isAdding;

        return $this;
    }

    /**
     * Create a command string to execute
     *
     * @param array $arguments An array of arguments
     * @param array $options   An array of options
     *
     * @return string
     */
    public function createCommandString($arguments = array(), $options = array())
    {
        if (!$this->subCommand) {
            throw new VersionControl_Git_Exception('You must specify "subCommand"');
        }

        $command = $this->git->getGitCommandPath().' '.$this->subCommand;

        $arguments = array_merge($this->arguments, $arguments);
        $options   = array_merge($this->options, $options);

        foreach ($options as $k => $v) {
            if (false === $v) {
                continue;
            }

            $isShortOption = (1 === strlen($k));

            if ($isShortOption) {
                $command .= ' -'.$k;
            } else {
                $command .= ' --'.$k;
            }

            if (true !== $v) {
                $command .= (($isShortOption) ? '' : '=') . $this->escapeshellarg($v);
            }
        }

        foreach ($arguments as $v) {
            $command .= ' ' . $this->escapeshellarg($v);
        }

        if ($this->doubleDash) {
            $command .= ' --';
        }

        return $command;
    }

    /**
     * Execute a created command and get result
     *
     * @param array $arguments An array of arguments
     * @param array $options   An array of options
     *
     * @return string
     */
    public function execute($arguments = array(), $options = array())
    {
        $command = $this->createCommandString($arguments, $options);

        $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();

        $envVars = $this->envVars;
        if (count($envVars) === 0) {
            $envVars = null;
        }

        $resource = proc_open(
            $command, $descriptorspec, $pipes, realpath($this->git->getDirectory()),
            $envVars
        );

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));
        if ($status) {
            $message = "Some errors in executing git command\n\n"
                     . "Output:\n"
                     . $stdout."\n"
                     . "Error:\n"
                     . $stderr;
            throw new VersionControl_Git_Exception($message);
        }

        return $this->stripEscapeSequence($stdout);
    }

    /**
     * Strip terminal escape sequences from the specified string
     *
     * @param string $string The string that will be trimmed
     *
     * @return string
     */
    public function stripEscapeSequence($string)
    {
        $string = preg_replace('/\e[^a-z]*?[a-z]/i', '', $string);

        return $string;
    }

    /**
     * Escape a single value in accordance with CommandLineToArgV() for Windows
     * @see https://docs.microsoft.com/en-us/previous-versions/17w5ykft(v=vs.85)
     */
    private function escapeshellarg($value)
    {
        $value = (string)$value;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            static $expr = '(
			[\x00-\x20\x7F"] # control chars, whitespace or double quote
		  | \\\\++ (?=("|$)) # backslashes followed by a quote or at the end
		)ux';

            if ($value === '') {
                return '""';
            }

            $quote = false;
            $replacer = function($match) use($value, &$quote) {
                switch ($match[0][0]) { // only inspect the first byte of the match

                    case '"': // double quotes are escaped and must be quoted
                        $match[0] = '\\"';
                    case ' ': case "\t": // spaces and tabs are ok but must be quoted
                    $quote = true;
                    return $match[0];

                    case '\\': // matching backslashes are escaped if quoted
                        return $match[0] . $match[0];

                    default: throw new VersionControl_Git_Exception(sprintf(
                        "Invalid byte at offset %d: 0x%02X",
                        strpos($value, $match[0]), ord($match[0])
                    ));
                }
            };

            $escaped = preg_replace_callback($expr, $replacer, (string)$value);

            if ($escaped === null) {
                throw preg_last_error() === PREG_BAD_UTF8_ERROR
                    ? new VersionControl_Git_Exception("Invalid UTF-8 string")
                    : new VersionControl_Git_Exception("PCRE error: " . preg_last_error());
            }

            return $quote // only quote when needed
                ? '"' . $escaped . '"'
                : $value;

        } else {
            return escapeshellarg($value);
        }
    }
}
