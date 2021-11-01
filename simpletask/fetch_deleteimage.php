<?php
  $filename = file_get_contents('php://input');
  // echo $_POST['listid'];

  unlink($filename);

?>
