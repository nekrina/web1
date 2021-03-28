<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->hset("taxi_car", "brand", "Волга");
$redis->hset("taxi_car", "model", "ГАЗ 3110");
$redis->hset("taxi_car", "Номер лицензии", "RO-01-PHP");
$redis->hset("taxi_car", "Год изготовления", 1999);
$redis->hset("taxi_car", "nr_starts", 0);



?>
<HTML>
<blockquote>
<p>
<?php
$list = "Список машин";
$redis->rpush($list, "Волга");
$redis->rpush($list, "lada");
$redis->lpush($list, "BMW");

echo "Количество доступных машин: " . $redis->llen($list) . "<br>";

$arList = $redis->lrange($list, 0, -1);
echo "<pre>";
print_r($arList);
echo "</pre>";

echo "Последняя машина в очереди: ";
echo $redis->rpop($list) . "<br>";

echo "Первая машина в очереди: ";
echo $redis->lpop($list) . "<br>";

if(isset($_POST['button_name'])){
     $redis->delete($redis->keys('*'));
    }
?>

</p></blockquote>

<form method="POST" action="">
<div style="display:inline-block">
 <input type="submit" value="Добавить волгу" name="but" class="gradient-button" >

  <input type = "submit" name="button_name" class="gradient-button" value = "Удалить" />
</div>

</form>

</HTML>
<style>

.gradient-button {
  text-decoration: none;
  display: inline-block;
  color: white;
  padding: 20px 30px;
  margin: 10px 20px;
  border-radius: 10px;
  font-family: 'Montserrat', sans-serif;
  text-transform: uppercase;
  letter-spacing: 2px;
  background-image: linear-gradient(to right, #9EEFE1 0%, #4830F0 51%, #9EEFE1 100%);
  background-size: 200% auto;
  box-shadow: 0 0 20px rgba(0, 0, 0, .1);
  transition: .5s;
 
}
.gradient-button:hover {
  background-position: right center;
}

blockquote {
margin: 0;
background: #BCE8EA;
color: #131314;
padding: 30px 30px 30px 90px;
position: relative;
font-family: 'Lato', sans-serif;
}

blockquote p {
margin-top: 0;
font-size: 24px;
font-weight: 300;
}
blockquote cite {
font-style: normal;
text-transform: uppercase;
}

</style>