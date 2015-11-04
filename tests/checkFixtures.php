<?php
if (!is_dir(dirname(__FILE__) . '/fixtures/001_VersionControl_Git'))
{
  if (!is_file(dirname(__FILE__) . '/fixtures.tar.gz'))
  {
    throw new Exception('You don\'t have fixtures.tar.gz. You need it to execute the unit test. Please download and expand it. (See README file)'.PHP_EOL);
  }
  else
  {
    throw new Exception('You need to expand fixtures.tar.gz to execute the unit test.'.PHP_EOL);
  }
}

