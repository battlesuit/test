<?php
namespace test_case\presenters;

/**
 * Description of presenter
 *
 * @author Tom
 */
class HtmlPresenter {  
  function write_header() {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Content-Type: text/html; charset=utf-8");
  }
  
  function render_summary($tests_completed = 0, $tests_processed = 0, $assertions_passed = 0, $assertions_failed = 0, $exceptions_thrown = 0) {
    $color = $assertions_failed > 0 ? 'red' : 'green';
    if($exceptions_thrown > 0) $color = 'orange';
    
    return "<div style=\"background: $color; color: white; padding: 10px; font-family: monospace\">$tests_completed/$tests_processed tests completed: $assertions_passed passed, $assertions_failed failed, $exceptions_thrown exceptions</div>";
  }
  
  function render_failures(array $failures) {
    $list = null;
    foreach($failures as $fail) {
      $message = $fail['message'];
      $func = $fail['info']['function'];
      $list .= "<li>Assertion Failed: $func => $message</li>";
    }
    
    return "<ol>$list</ol>";
  }
  
  function render_exceptions(array $exceptions) {
    $list = null;
    foreach($exceptions as $ex) {
      $message = $ex['message'];
      $func = $ex['info']['function'];
      $list .= "<li>Exception thrown: $func => $message</li>";
    }
    
    return "<ol>$list</ol>";
  }

  function render($name, $recorder) {
    $summary = $this->render_summary($recorder->tests_completed(), $recorder->tests_recorded(), $recorder->assertions_passed(), $recorder->assertions_failed(), $recorder->exceptions_thrown());
    $failures = $this->render_failures($recorder->failed_assertions());
    $exceptions = $this->render_exceptions($recorder->exceptions());
    return "<!DOCTYPE html><html><head><title>$name</title></head><body>
    <h1>$name</h1>
    $failures
    $exceptions
    $summary
    </body></html>";
  }
  
  function present($name, $recorder) {
    $this->write_header();
    echo($this->render($name, $recorder));
  }
}
?>