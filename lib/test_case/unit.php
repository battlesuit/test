<?php
namespace test_case;

class Unit extends Base {
  public $bench_dir = 'bench';
  
  function load_fixtures() {
    foreach(func_get_args() as $name) { 
      $file = $this->bench_dir()."/fixtures/$name.php"; 
      require_once $file;
    }
  }
  
  function load_mocks() {
    foreach(func_get_args() as $name) { 
      $file = $this->bench_dir()."/mocks/{$name}_mock.php";    
      require_once $file;
    }
  }
  
  function bench_dir() {
    return $this->bench_dir;
  }
}
?>