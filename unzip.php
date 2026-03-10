<?php

$zip = new ZipArchive;
$file = 'Archive.zip';

if ($zip->open($file) === TRUE) {
    $zip->extractTo('.');
    $zip->close();
    echo "Archive extracted successfully";
} else {
    echo "Failed to open archive";
}
?>