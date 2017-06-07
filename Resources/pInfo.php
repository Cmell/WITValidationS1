<?php
// This script includes functions to generate new pids and
// save condition information to the pid file.

function getNewPID($pidFile, $gunKey, $nogunKey) {
  if (($fp = fopen($pidFile, "r+")) === FALSE) {
    throw new Exception("Couldn't open pid file!");
  }

  $maxTries = 10;
  $numTries = 0;
  $gotLock = FALSE;
  while (!$gotLock && $numTries < $maxTries) {
    $numTries++;
    // figure out the new id and write it.
    $gotLock = flock($fp, LOCK_EX);
    if ($gotLock) {break;}
    sleep(1);
  }
  if ($gotLock) {
    $largestPid = 0;
    // Figure out the largest id and add one to it.
    while (($data = fgetcsv($fp, 1000, ",")) !== FALSE) {
      if ($largestPid < (int)$data[0]) {
        $largestPid = (int)$data[0];
      }
    }
    $pid = $largestPid + 1;
    date_default_timezone_set('America/Denver');
    $date = date ('m-d-Y H:i:s');

    // Write the id and condition information to the pid file.
    $newFields = array($pid, $gunKey, $nogunKey, $date);
    fputcsv($fp, $newFields);

    // close the file connection and lock
    flock($fp, LOCK_UN);
    fclose($fp);

    // return it
    return($pid);
  } else {
    throw new Exception("No lock on pid file!");
  }
}

function getNewTestingPID($pidFile, $gunKey, $nogunKey) {
  if (($fp = fopen($pidFile, "r+")) === FALSE) {
    throw new Exception("Couldn't open pid file!");
  }

  $maxTries = 10;
  $numTries = 0;
  $gotLock = FALSE;
  while (!$gotLock && $numTries < $maxTries) {
    $numTries++;
    // figure out the new id and write it.
    $gotLock = flock($fp, LOCK_EX);
    if ($gotLock) {break;}
    sleep(1);
  }
  if ($gotLock) {
    $smallestPid = 0;
    // Figure out the smallest id and subtract one from it.
    while (($data = fgetcsv($fp, 1000, ",")) !== FALSE) {
      if ($smallestPid > (int)$data[0]) {
        $smallestPid = (int)$data[0];
      }
    }
    $pid = $smallestPid - 1;
    date_default_timezone_set('America/Denver');
    $date = date ('m-d-Y H:i:s');

    // Write the id and condition information to the pid file.
    $newFields = array($pid, $gunKey, $nogunKey, $date);
    fputcsv($fp, $newFields);

    // close the file connection and lock
    flock($fp, LOCK_UN);
    fclose($fp);

    // return it
    return($pid);
  } else {
    throw new Exception("No lock on pid file!");
  }
}
?>
