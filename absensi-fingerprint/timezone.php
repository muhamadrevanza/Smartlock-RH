<?php
include 'koneksi.php';
$script_tz = date_default_timezone_get();

if (strcmp($script_tz, ini_get('date.timezone'))){
    echo 'Script timezone differs from ini-set timezone.';
    echo date_default_timezone_get();
} else {
    echo 'Script timezone and ini-set timezone match.';
}
?>