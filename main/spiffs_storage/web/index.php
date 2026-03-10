<?php
// most basic way to locally serve a webpage that is stored on the SoC
// NOTE: This does not simulate xhr traffic
$step = 0;

$steps = [
    "sta_setup.html",
    "server_setup.html",
    "overview.html",
];

if ( array_key_exists( $step, $steps ) && is_file( $steps[$step] ) ) {
    include $steps[$step];
    exit();
}
   
die("Invalid page");
?>