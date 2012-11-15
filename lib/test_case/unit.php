<?php
namespace test_case;

class Unit extends Base {
  public $bench_dir = './bench';
  
  function load_fixtures() {
    foreach(func_get_args() as $name) { 
      $file = $this->bench_dir()."/fixtures/$name.php";
      if(!file_exists($file)) die("$name fixture not found");
      
      require_once $file;
    }
  }
  
  function load_mocks() {
    foreach(func_get_args() as $name) { 
      $file = $this->bench_dir()."/mocks/{$name}_mock.php";
      if(!file_exists($file)) die("$name mock not found");
      
      require_once $file;
    }
  }
  
  function bench_dir() {
    return $this->bench_dir;
  }
}
?>