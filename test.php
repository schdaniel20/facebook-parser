<?php

require_once 'vendor/autoload.php';

use Cylex\Facebook\Parser\DataSource;
use Cylex\Facebook\Parser\Parser;
use Cylex\Facebook\Parser\DataTarget;

$source = [
    "table" => "all_facebook_RESULTS",
    "user" => "root",
    "password" => "root",
    "dsn" => "mysql:host=localhost;dbname=fb;charset=utf8"    
];

//target config
$target = [
    "table" => "datasyncTest",
    "user" => "root",
    "password" => "root",
    "dsn" => "mysql:host=localhost;dbname=fb;charset=utf8"    
];

$sessionID = 13308;

$source = new DataSource($source, $sessionID);
$parser = new Parser($sessionID);
$target = new DataTarget($target, $sessionID);
$target->init();
$container = [];

while(1) {
    
    $source->getNext($container, 'id');    
    if(count($container['ids']) == 0) break;
    
    foreach ($container['result'] as $res) {
        $parser->init($res);        
        $store = $parser->parse($res);
        $target->save($store);
    }
}

