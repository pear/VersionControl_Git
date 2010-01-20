<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../'));

require_once 'PHPUnit/Framework.php';

require_once './VersionControl_GitTest.php';
require_once './VersionControl_Git_ComponentTest.php';
require_once './VersionControl_Git_ObjectTest.php';
require_once './VersionControl_Git_Util_CommandTest.php';

class AllTest
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite('');

    $suite->addTestSuite('VersionControl_GitTest');
    $suite->addTestSuite('VersionControl_Git_ComponentTest');
    $suite->addTestSuite('VersionControl_Git_ObjectTest');
    $suite->addTestSuite('VersionControl_Git_Util_CommandTest');

    return $suite;
  }
}
