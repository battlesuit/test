<!DOCTYPE html>
<html>
  <head>
    <title>Testcase results</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <style type="text/css">
      body {
        font: 10pt "Courier New";
      }

      .result {
        padding: 5px;
      }

      .success_result {
        background: green;
        color: white;
      }

      .failure_result {
        background: red;
        color: white;
      }

      .info_result {
        background: khaki;
        color: gray;
      }
    </style>
  </head>
  <body>
    <div class="success_result"><?= $tests_completed ?>/<?= $num_tests ?> test cases completed: <?= count($passed_tests) ?> passes, <?= count($failed_tests) ?> failed</div>
  </body>
</html>