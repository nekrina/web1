<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$arList = $redis->set(session_id(), json_encode(array('uname'=>'messi fan')));
print_r($arList);
?>
