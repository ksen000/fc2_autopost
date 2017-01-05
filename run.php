<?php

while (1) {
  sleep(180 * 60); // wait 3 hours..
  $somearg = escapeshellarg('blah');
  exec("php autopost.php $somearg > /dev/null &");
}

?>