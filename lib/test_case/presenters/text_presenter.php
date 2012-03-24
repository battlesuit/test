<?php
namespace test_case\presenters;

/**
 * Description
 *
 * PHP Version 5.4+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @package test
 */
class TextPresenter {
  function render_summary($tests_completed = 0, $tests_processed = 0, $assertions_passed = 0, $assertions_failed = 0, $exceptions_thrown = 0) {
    $color = $assertions_failed > 0 ? 'red' : 'green';
    if($exceptions_thrown > 0) $color = 'orange';
    
    return "$tests_completed/$tests_processed tests completed: $assertions_passed passes, $assertions_failed fails, $exceptions_thrown exceptions";
  }
  
  function render_exceptions(array $exceptions) {
    $list = null;
    foreach($exceptions as $index => $info) {
      $num = $index + 1;
      extract($info);
      $test_class_name = basename($test_class);
      $thrower = $info['trace'][0];
      
      if(!isset($thrower['class']) and isset($thrower['function'])) {
        $thrown_in = $thrower['function'];
      } elseif(isset($thrower['class']) and isset($thrower['function'])) {
        $thrown_in = $thrower['class']."#".$thrower['function'];
      } else {
        $thrown_in = 'global space';
      }
      
      $thrower_class = $thrower['class'];
      $thrower_function = $thrower['function'];
      
      $list .= ":$num $test_class_name#test_$test_name \"$message\"
 ∟ thrown in [$thrown_in]
 ∟ file: $file($line)
      
";
    }
    
    return $list;
  }
  
  function render_fails(array $fails) {
    $list = null;
    foreach($fails as $index => $info) {
      $num = $index + 1;
      extract($info);
      $test_class_name = basename($test_class);

      
      $list .= ":$num $test_class_name#test_$test_name \"$message\"
 ∟ file: $file($line)
      
";
    }
    return $list;
  }
  
  function render($name, $recorder) {
    $fails = $this->render_fails($recorder->failed_assertions());
    $exceptions = $this->render_exceptions($recorder->exceptions());
    $summary = $this->render_summary($recorder->tests_completed(), $recorder->tests_recorded(), $recorder->assertions_passed(), $recorder->assertions_failed(), $recorder->exceptions_thrown());
    
    if(isset($exceptions)) {
      $exceptions = "Exceptions occurred:\n$exceptions\n";
    }
    
    if(isset($fails)) {
      $fails = "Fails occurred:\n$fails\n";
    }
    
    return $exceptions.$fails."------\n".$summary;
  }
  
  function present($name, $recorder) {
    echo($this->render($name, $recorder));
  }
}
?>