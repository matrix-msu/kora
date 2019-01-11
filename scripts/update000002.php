<?php
$version = \App\Version::all()->first();
if($version->version=='3.0.1')
    $version->version = '3.0.2';