<?php
namespace test_bench;
use test_case\Recorder;
use test_case\presenters\TextPresenter;
use test_case\presenters\HtmlPresenter;
use test_case\Base as TestCase;

/**
 * Description of suite
 *
 * @author Tom
 */
class Base {
  private $tests = array();
  protected $title;
  
  function __construct($title = null) {
    if(isset($title)) $this->title = $title;
    if(method_exists($this, 'initialize')) $this->initialize();
  }
  
  function add_test(TestCase $test) {
    $this->tests[$test->name()] = $test;
  }
  
  function run($recorder = null) {
    if(!isset($recorder)) $recorder = new Recorder();
    
    foreach($this->tests as $test) {
      $test->run($recorder);
    }
    
    return $recorder;
  }
  
  function run_test($name) {
    $recorder = new Recorder();
    
    if(isset($this->tests[$name])) {
      $this->tests[$name]->run($recorder);
    }
    
    return $recorder;
  }
  
  function run_and_present_as_html($recorder = null) {
    $recorder = $this->run($recorder);
    
    $presenter = new HtmlPresenter();
    $presenter->present($this->title, $recorder);
  }
  
  function run_and_present_as_text($recorder = null) {
    $recorder = $this->run($recorder);
    
    $presenter = new TextPresenter();
    $presenter->present($this->title, $recorder);
  }
}
?>