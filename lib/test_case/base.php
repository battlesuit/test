<?php
namespace test_case;
use test\Error;

class SetUpError extends Error {}
class TearDownError extends Error {}
class AssertionFailure extends Error {}

/**
 * Top-Level testcase class
 *
 * PHP Version 5.4+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @package test
 */
abstract class Base {
  
  /**
   * Test recorder
   * Documents all failures, errors and successes
   *
   * @access protected
   * @var Recorder
   */
  protected $recorder;
  
  final function __construct() {
    if(method_exists($this, 'initialize')) $this->initialize();
  }

  /**
   * Reads the name of the test
   * 
   */
  function name() {
    $class = get_class($this);
    if(strpos($class, '\\') !== false) $class = substr(strrchr($class, '\\'), 1);
    $name = str_replace('Test', '', $class);   
    
    return static::str_underscore($name);
  }
  
  /**
   * Convert camelcased word into system readable underscored string
   * All whitespaces and backslashses of namespaces will be replaced by one _
   *
   * @static
   * @param string $string
   * @return string
   */
  static function str_underscore($string) {
    $string = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $string);
    $string = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $string);
    $string = preg_replace('/\s+/', '_', $string);
    return strtolower($string);
  }
  
  /**
   * Prepare error handler
   *
   * @access protected
   */
  protected function prepare_error_handler() {
    //set_exception_handler(array($this, 'handle_exception'));
    set_error_handler(array($this, 'handle_error'));
  }
  
  /**
   * Catches exceptions during the recording
   *
   * @access protected
   * @param \Exception $exception
   */
  protected function catch_exception(\Exception $exception) {
    if($exception instanceof AssertionFailure) {
      $this->recorder->fail_assertion($this->prepare_info_for($exception));
    } else {    
      $this->recorder->record_exception($this->prepare_info_for($exception));
    }
  }
  
  /**
   * Converts the exception into a valid info array for the recorder
   *
   * @access protected
   * @param \Exception $exception
   * @return array
   */
  protected function prepare_info_for(\Exception $exception) {
    $trace = $exception->getTrace();
    $info = array();
    $info['line'] = $exception->getLine();
    $info['file'] = $exception->getFile();
    $info['exception_class'] = get_class($exception);
    $info['trace'] = $trace;
    $info['trace_as_string'] = $exception->getTraceAsString();
    $info['message'] = $exception->getMessage();
    
    foreach($trace as $index => $i) {
      if(strpos($i['function'], 'test_') === 0) {
        $info['test_class'] = $i['class'];
        $info['test_method'] = $i['function'];
        $info['test_name'] = preg_replace('/^test_/', '', $i['function']);
        
        break;
      }
    }
    
    return $info;
  }
  
  /**
   * Handles errors by throwing an \ErrorException
   *
   * @access public
   * @param int $number
   * @param string $error_message
   * @param string $error_file
   * @param int $error_line
   * @param array $context
   */
  function handle_error($number, $error_message, $error_file, $error_line, $context) {
    throw new \ErrorException($error_message, 0, $number, $error_file, $error_line);
  }
  
  /**
   * Runs the testcase
   * 
   * @access public
   * @param Recorder $recorder
   */
  function run($recorder) {
    $this->recorder = $recorder;
    $this->prepare_error_handler();
    $test_methods = array();
    
    if(property_exists($this, 'isolate')) {
      foreach($this->isolate as $tn) {
        $test_methods[] = "test_$tn";
      }
    } else {
      $test_methods = $this->collect_test_methods();
    }
    
    try {
      # try set-up method
      
      //if(method_exists($this, 'set_up')) $this->set_up();
    } catch(\Exception $e) {
      throw new SetUpError($e->getMessage(), $e->getCode());
    }
    
    try {
      # try tear-down method
      //if(method_exists($this, 'tear_down')) $this->tear_down();
      //if(method_exists($this, 'shut')) $this->shut();
    } catch(\Exception $e) {
      throw new TearDownError($e->getMessage(), $e->getCode());
    }
    
    if(method_exists($this, 'boot')) $this->boot();
    
    foreach($test_methods as $method) {  
      $recorder->record_test();
      $this->process_test($method);      
      $recorder->complete_test();
    }
    
    if(method_exists($this, 'shut')) $this->shut();
  }
  
  /**
   * Processing a single test
   * Including setting up and tearing down functionality
   *
   * @access protected
   * @param string $name
   */
  protected function process_test($name) {
    if(method_exists($this, 'set_up')) $this->set_up();
    
    try {
      call_user_func(array($this, $name));
    } catch(\Exception $exception) {
      $this->catch_exception($exception);
    }
    
    if(method_exists($this, 'tear_down')) $this->tear_down();
  }
  
  /**
   * Collects and returns all valid test methods for this case
   *
   * @access public
   * @return array
   */
  function collect_test_methods() {
    $methods = array();
    foreach(get_class_methods($this) as $method_name) {
      if($this->is_test_method($method_name)) {
        $methods[] = $method_name;
      }
    }
    return $methods;
  }
  
  /**
   * Do we have a test method here?
   *
   * @access public
   * @param string $name
   * @return boolean
   */
  function is_test_method($name) {
    return strtolower(substr($name, 0, 5)) == 'test_';
  }

  /**
   * Trigger for a passed assertion
   * 
   * @access public
   * @param string $message
   */
  function pass_assertion($message = null) {
    $this->recorder->pass_assertion($message);
  }
  
  /**
   * Trigger for a failed assertion
   * 
   * @access public
   * @param string $message
   */
  function fail_assertion($message = null) {
    $trace = debug_backtrace();
    $last_info = $trace[2];
    
    throw new AssertionFailure($message, 0, 0, $last_info['file'], $last_info['line']);
  }
  
  /**
   * Evaluates a new assertion
   * 
   * @access public
   * @param boolean $true_expectation
   * @param string $message
   */
  function assert($true_expectation, $message = null) {
    if((boolean)$true_expectation) {
      $this->pass_assertion();
    } else $this->fail_assertion($message);
  }
  
  /**
   * Assert array key existance
   *
   * @access public
   * @param array $stack
   * @param mixed $key
   * @param string $message
   */
  function assert_key_exists($key, array $stack, $message = 'Expects array-key to exist') {
    $this->assert(array_key_exists($key, $stack), $message);
  }
  
  /**
   * Assert array key existance
   *
   * @access public
   * @param array $stack
   * @param mixed $key
   * @param string $message
   */
  function assert_key_missing($key, array $stack, $message = 'Expects array-key not to exist') {
    $this->assert(!array_key_exists($key, $stack), $message);
  }
  
  /**
   * Assert existance of an array value
   *
   * @access public
   * @param array $stack
   * @param mixed $value
   * @param string $message
   */
  function assert_includes($value, array $stack, $message = 'Array does not include the given value') {
    $this->assert(in_array($value, $stack), $message);
  }
  
  /**
   * Assert strict trueness
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_true($value, $message = 'True expected') {
    $this->assert($value === true, $message);
  }
  
  /**
   * Assert strict falseness
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */ 
  function assert_false($value, $message = 'False expected') {
    $this->assert($value === false, $message);
  }
  
  /**
   * Assert set-state of value
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_set($value, $message = 'Value has to be set') {
    $this->assert(isset($value), $message);
  }
  
  /**
   * Assert notset-state of value
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_not_set($value, $message = 'Value has not to be set') {
    $this->assert(!isset($value), $message);
  }
  
  /**
   * Assert empty-state of value
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_empty($value, $message = 'Empty value expected') {
    $this->assert(empty($value), $message);
  }
  
  /**
   * Assert empty-array
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_empty_array($value, $message = 'Empty array value expected') {
    $this->assert(is_array($value) and empty($value), $message);
  }
  
  /**
   * Assert empty-string
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_empty_string($value, $message = 'Empty string value expected') {
    $this->assert(is_string($value) and empty($value), $message);
  }
  
  /**
   * Assert notempty-state of value
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_present($value, $message = 'Present value expected') {
    $this->assert(!empty($value), $message);
  }
  
  /**
   * Assert callable
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */
  function assert_callable($value, $message = 'Callable expected') {
    $this->assert(is_callable($value), $message);
  }
  
  /**
   * Assert true array
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */
  function assert_array($value, $message = 'Array expected') {
    $this->assert(is_array($value), $message);
  }
  
  /**
   * Assert zeroness of value
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */
  function assert_zero($value, $message = 'Zero expected') {
    $this->assert($value === 0, $message);
  }
  
  /**
   * Assert true integer
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */
  function assert_int($value, $message = 'Integer expected') {
    $this->assert(is_int($value), $message);
  }
  
  /**
   * Assert true string
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_string($value, $message = 'String expected') {
    $this->assert(is_string($value), $message);
  }
  
  /**
   * Assert true object
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */  
  function assert_object($value, $message = 'Object expected') {
    $this->assert(is_object($value), $message);
  }
  
  /**
   * Assert instanceof 
   *
   * @access public
   * @param mixed $value
   * @param string $of
   * @param string $message
   */
  function assert_instanceof($value, $of, $message = 'Object missmatch') {
    $this->assert(($value instanceof $of), $message);
  }
  
  /**
   * Assert is-null 
   *
   * @access public
   * @param mixed $value
   * @param string $message
   */
  function assert_null($value, $message = 'Null expected') {
    $this->assert(is_null($value), $message);
  }
  
  /**
   * Assert strict equality (===)
   *
   * @access public
   * @param mixed $value
   * @param mixed $to
   * @param string $message
   */
  function assert_equality($equal, $to, $message = 'Equality expected') {
    $this->assert(($equal === $to), $message);
  }
  
  # aliases for assert_equality()
  function assert_equal($equal, $to, $message = 'Equality expected') {
    return $this->assert_equality($equal, $to, $message);
  }
  
  function assert_eq($equal, $to, $message = 'Equality expected') {
    return $this->assert_equality($equal, $to, $message);
  }
  
  function assert_thrown_exception($object_or_class, $method, array $arguments = array(), $message = 'Exception expected') {
    try {
      call_user_func_array(array($object_or_class, $method), $arguments);
    } catch(\Exception $e) {
      return $this->pass_assertion('Exception thrown');
    }
    
    $this->fail_assertion($message);
  }
}
?>