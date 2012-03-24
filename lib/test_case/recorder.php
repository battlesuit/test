<?php
namespace test_case;

/**
 * Description of score
 *
 * @author Tom
 */
class Recorder {
  private $tests_recorded = 0;
  private $tests_completed = 0;
  
  private $passed_assertions = array();
  private $failed_assertions = array();
  private $exceptions = array();
  private $errors = array();
  
  function complete_test() {
    $this->tests_completed++;
  }
  
  function record_test() {
    $this->tests_recorded++;
  }
  
  function tests_recorded() {
    return $this->tests_recorded;
  }
  
  function tests_completed() {
    return $this->tests_completed;
  }
  
  function assertions_passed() {
    return count($this->passed_assertions);
  }
  
  function assertions_failed() {
    return count($this->failed_assertions);
  }
  
  function exceptions_thrown() {
    return count($this->exceptions);
  }
  
  function passed_assertions() {
    return $this->passed_assertions;
  }
  
  function failed_assertions() {
    return $this->failed_assertions;
  }
  
  function exceptions() {
    return $this->exceptions;
  }
  
  function errors() {
    return $this->errors;
  }
  
  function record_exception(array $info = array()) {
    $this->exceptions[] = $info;
  }
  
  function pass_assertion($message = null, array $info = array()) {
    $this->passed_assertions[] = compact('message', 'info');
  }
  
  function fail_assertion(array $info = array()) {
    $this->failed_assertions[] = $info;
  }
}
?>