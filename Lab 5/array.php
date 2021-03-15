<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->hset("taxi_car", "brand", "Волга");
$redis->hset("taxi_car", "model", "ГАЗ 3110");
$redis->hset("taxi_car", "Номер лицензии", "RO-01-PHP");
$redis->hset("taxi_car", "Год изготовления", 1999);
$redis->hset("taxi_car", "nr_starts", 0);
/*
$redis->hmset("taxi_car", array(
    "brand" => "Волга",
    "model" => "ГАЗ 3110",
    "licence number" => "RO-01-PHP",
    "year of fabrication" => 1999,
    "nr_stats" => 0)
);
*/
echo "Номер лицензии: " .
    $redis->hget("taxi_car", "Номер лицензии") . "<br>";

// удалить номер лицензии
$redis->hdel("taxi_car", "Номер лицензии");

// начало роста числа
$redis->hincrby("taxi_car", "nr_starts", 1);

$taxi_car = $redis->hgetall("taxi_car");
echo "Вся информация о такси";
echo "<pre>";
var_dump($taxi_car);
echo "</pre>";
?>

