<?php
/**
 * Initializing test bench
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage TestBench
 */
namespace {
  foreach(array('error') as $name) {
    require_once __DIR__."/test/$name.php";
  }
  
  foreach(array('base') as $name) {
    require_once __DIR__."/test_bench/$name.php";
  }
  
  foreach(array('base', 'recorder') as $name) {
    require_once __DIR__."/test_case/$name.php";
  }
  
  foreach(array('text_presenter', 'html_presenter') as $name) {
    require_once __DIR__."/test_case/presenters/$name.php";
  }
  
  # aliasing to global scope
  class_alias('test_case\Base', 'TestCase');
  class_alias('test_bench\Base', 'TestBench');
}
?>