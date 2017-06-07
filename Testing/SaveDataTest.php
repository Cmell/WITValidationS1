<?php
// $_POST contains the info passed to the script.
$filename = "./".$_POST['filename'];
$data = $_POST['filedata'];
$nTrial = $_POST['nTrial'];

// write to disk
file_put_contents($filename, $data);

echo $nTrial;
?>
