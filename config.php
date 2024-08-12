<?php

// annual L.A.S.S.I.E. API configuration
$lassie = [
    "api" => "",
    "key" => ""
];

// telegram bot for error reporting
$telegram = [
    "key" => "", // @
    "admin" => "", // @
];

// api url build
$telegram['api'] = "https://api.telegram.org/bot{$telegram['key']}/sendMessage";

// base path of file operations
$basepath = "/home/ef-web/sites/www.eurofurence.org/data/lf";
