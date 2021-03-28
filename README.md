# web1. Реализация разделяемого хранилища данных с использованием Redis
Применение данного хранилища рассматривается для реализации хранения разделяемого состояния между экземплярами веб-сервисов. Также, рассматривается концепция no sql хранилища данных.
Для реализации данной лабораторной работы требуется:
1.	Пройти интерактивный redis tutorial
2.	Запустить redis
3.	Доработать приложение из предыдущей лабораторной работы таким образом, чтобы счетчик входящих запросов хранился в redis.
## Ход работы.
1. Скачать и запустить redis
2. Запустить OS Panel
3. Код программы для взаимодействия с redis
```
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
//Количество элементов в списке
echo "Количество доступных машин: " . $redis->llen($list) . "<br>";
//Вывод списка
$arList = $redis->lrange($list, 0, -1);
echo "<pre>";
print_r($arList);
echo "</pre>";
//Отображение 1 элемента в списке
echo "Последняя машина в очереди: ";
echo $redis->rpop($list) . "<br>";
//Отображние последнего элемента в списке
echo "Первая машина в очереди: ";
echo $redis->lpop($list) . "<br>";
//Отчистка
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
```

# web2. Реализация партиционирования с использованием Postgres
## Задание:
Для реализации данной лабораторной работы требуется:
1.	Необходимо запустить СУБД с поддержкой партиционирования (например, postgres)
2.	Создать таблицу с использованием партиционирования
3.	Написать запросы:
    *	вставки данных
    *	добавления и удаления партиций
    *	создания глобальных и локальных индексов
    *	выборки данных и использованием индексов
## Выполнение работы:
1. Создайте таблицу measurement как секционированную таблицу с предложением PARTITION BY, указав метод разбиения (в нашем случае RANGE) и список столбцов, которые будут образовывать ключ разбиения.
```
CREATE TABLE measurement (
    city_id         int not null,
    logdate         date not null,
    peaktemp        int,
    unitsales       int
) PARTITION BY RANGE (logdate);
```

2. Для таблиц-секций нет необходимости определять ограничения с условиями, задающими границы значений. Нужные ограничения секций выводятся неявно из определения границ секции, когда требуется к ним обратиться.
```
CREATE TABLE measurement_y2006m02 PARTITION OF measurement
    FOR VALUES FROM ('2006-02-01') TO ('2006-03-01');
CREATE TABLE measurement_y2006m03 PARTITION OF measurement
    FOR VALUES FROM ('2006-03-01') TO ('2006-04-01');
...
CREATE TABLE measurement_y2007m11 PARTITION OF measurement
    FOR VALUES FROM ('2007-11-01') TO ('2007-12-01');
CREATE TABLE measurement_y2007m12 PARTITION OF measurement
    FOR VALUES FROM ('2007-12-01') TO ('2008-01-01')
    TABLESPACE fasttablespace;
CREATE TABLE measurement_y2008m01 PARTITION OF measurement
    FOR VALUES FROM ('2008-01-01') TO ('2008-02-01')
    WITH (parallel_workers = 4)
    TABLESPACE fasttablespace;
```
Для реализации вложенного секционирования укажите предложение PARTITION BY в командах, создающих отдельные секции, например:
CREATE TABLE measurement_y2006m02 PARTITION OF measurement
```
FOR VALUES FROM ('2006-02-01') TO ('2006-03-01')
PARTITION BY RANGE (peaktemp);
```
    
3. Создайте индекс по ключевому столбцу(ам), а также любые другие индексы, которые вы хотели бы иметь в каждой секции. (Индекс по ключу, строго говоря, не необходим, но в большинстве случаев он будет полезен. Если вы хотите, чтобы значения ключа были уникальны, вам следует также создать ограничения уникальности или первичного ключа для каждой секции.)
```
CREATE INDEX ON measurement_y2006m02 (logdate);
CREATE INDEX ON measurement_y2006m03 (logdate);
...
CREATE INDEX ON measurement_y2007m11 (logdate);
CREATE INDEX ON measurement_y2007m12 (logdate);
CREATE INDEX ON measurement_y2008m01 (logdate);
```
Самый лёгкий способ удалить старые данные — просто удалить секцию, ставшую ненужной:
```DROP TABLE measurement_y2006m02;```
Ещё один часто более предпочтительный вариант — убрать секцию из главной таблицы, но сохранить возможность обращаться к ней как к самостоятельной таблице:

```ALTER TABLE measurement DETACH PARTITION measurement_y2006m02;```

Для каждой секции создайте индекс по ключевому столбцу(ам), а также любые другие индексы по своему усмотрению.
```
CREATE INDEX measurement_y2006m02_logdate ON measurement_y2006m02 (logdate);
CREATE INDEX measurement_y2006m03_logdate ON measurement_y2006m03 (logdate);
CREATE INDEX measurement_y2007m11_logdate ON measurement_y2007m11 (logdate);
CREATE INDEX measurement_y2007m12_logdate ON measurement_y2007m12 (logdate);
CREATE INDEX measurement_y2008m01_logdate ON measurement_y2008m01 (logdate);
```
# web4. Реализация запуска приложения с использованием Docker
Во всех облачных платформах имеется возможность запуска приложений с использованием Kubernetes. Рассмотрение Docker является предварительным шагом перед запуском приложения в Kubernetes.
Для реализации данной лабораторной работы требуется:
1.	Пройти interactive tutorial по Docker
2.	Создать веб-сервис
3.	Создать Dockerfile и запустить приложение в Docker
4.	Создать docker-compose.yaml и запустить несколько контейнеров с использованием docker-compose
## Выполнение работ
Создайте новую директорию mkdir mydockerbuild.
```
$ mkdir mydockerbuild
```
Этот каталог служит в качестве "контекста" для сборки. Контекст просто означает, что он содержит все, что вам нужно, чтобы построить свой образ.

Войдите в созданную директорию.
```
$ cd mydockerbuild
```
Сейчас она пуста.

Создайте Dockerfile в директории командой touch Dockerfile.
```
$ touch Dockerfile
```
Команда создает новый файл но ничего не выводит в консоль. Используйте команду ls Dockerfile что бы убедиться что файл создан.
```
$ ls Dockerfile
Dockerfile
```
Откройте Dockerfile редакторе кода типа Atom или Sublime, или в текстовом редакторе, таком как vi, или nano (https://www.nano-editor.org/).

Добавьте следующие строки:
```
FROM docker/whalesay:latest
```

Ключевое слово FROM говорит Docker какой образ будет базовым. Образ whalesay уже имеет программу cowsay по этому мы возьмем его за основу.

Теперь добавим программу fortunes в наш образ.
```
RUN apt-get -y update && apt-get install -y fortunes
```
Программа fortunes имеет команду которая выводит мудрые фразы для нашего кита. Итак, первым шагом устанавливаем fortunes. Эта строка устанавливает программу в образ.

После того как мы добавили в образ нужную нам программу, нужно добавить инструкцию запуска программы при старте контейнера.
```
  CMD /usr/games/fortune -a | cowsay
```
Эта строка говорит программе fortune передать фразу программе cowsay.

Теперь ваш Dockerfile должен выглядеть так:
```
FROM docker/whalesay:latest
RUN apt-get -y update && apt-get install -y fortunes
CMD /usr/games/fortune -a | cowsay
```
Сохраните и закройте Dockerfile.

На этом этапе, в Dockerfile добавлены 
Создание образа из Dockerfile

Находясь в командной строке, убедитесь что Dockerfile расположен в текущем каталоге cat Dockerfile
```
$ cat Dockerfile
FROM docker/whalesay:latest

RUN apt-get -y update && apt-get install -y fortunes

CMD /usr/games/fortune -a | cowsay
```
Теперь, соберем ваш новый образ набрав в терминале команду docker build -t docker-whale . (на забудьте точку через пробел в конце).
```
$ docker build -t docker-whale .
Sending build context to Docker daemon 158.8 MB
...snip...
Removing intermediate container a8e6faa88df3
Successfully built 7d9495d03763
```
Выполнение команды занимает несколько секунд. Перед тем как вы начнете делать что либо с новым образом, уделите минуту что бы побольше узнать о процессе сборки образа из Dockerfile.
Шаг 3: Узнайте больше о процессе сборки

Команда docker build -t docker-whale . открывает Dockerfile в текущей директории, и создает образ docker-whale на вашем компьютере. Выполнение команды занимает около минуты и вывод команды выглядит действительно большим. В этом разделе вы узнаете что означают эти сообщения.

Первым делом Докер проверяет все ли необходимое есть для создания образа.
```
Sending build context to Docker daemon 158.8 MB
```
Затем, Docker загружает образ whalesay. У нас уже есть этот образ на локальном компьютере как вы можете помнить из предыдущего урока. Так что Docker не нужно скачивать его повторно.

Step 1 : 
```FROM docker/whalesay:latest
 ---> fb434121fc77
```
Docker переходит к следующему шагу обновлению пакетного менеджера apt-get. В процессе выводится много строчек, нет нужды приводить их тут.


Step 2 : 
```RUN apt-get -y update && apt-get install -y fortunes
 ---> Running in 27d224dfa5b2
Ign http://archive.ubuntu.com trusty InRelease
Ign http://archive.ubuntu.com trusty-updates InRelease
Ign http://archive.ubuntu.com trusty-security InRelease
Hit http://archive.ubuntu.com trusty Release.gpg
....snip...
Get:15 http://archive.ubuntu.com trusty-security/restricted amd64 Packages [14.8 kB]
Get:16 http://archive.ubuntu.com trusty-security/universe amd64 Packages [134 kB]
Reading package lists...
---> eb06e47a01d2
```
Затем Docker устанавливает fortunes.

```
Removing intermediate container e2a84b5f390f
```
Step 3 : 
```RUN apt-get install -y fortunes
 ---> Running in 23aa52c1897c
Reading package lists...
Building dependency tree...
Reading state information...
The following extra packages will be installed:
  fortune-mod fortunes-min librecode0
Suggested packages:
  x11-utils bsdmainutils
The following NEW packages will be installed:
  fortune-mod fortunes fortunes-min librecode0
0 upgraded, 4 newly installed, 0 to remove and 3 not upgraded.
Need to get 1961 kB of archives.
After this operation, 4817 kB of additional disk space will be used.
Get:1 http://archive.ubuntu.com/ubuntu/ trusty/main librecode0 amd64 3.6-21 [771 kB]
...snip......
Setting up fortunes (1:1.99.1-7) ...
Processing triggers for libc-bin (2.19-0ubuntu6.6) ...
 ---> c81071adeeb5
Removing intermediate container 23aa52c1897c
```
Итак, Docker завершает создание образа и сообщает об этом в консоль.

Step 4 : 
```CMD /usr/games/fortune -a | cowsay
 ---> Running in a8e6faa88df3
 ---> 7d9495d03763
Removing intermediate container a8e6faa88df3
Successfully built 7d9495d03763
```
Шаг 4: Запуск нового образа

В этом шаге вы проверите образы на вашем компьютере, а за тем запустите ваш новый образ.

Введите и выполните команду docker images в командной строке.

Эта команда, как вы можете помнить, выводит список локальных образов в системе.
```
$ docker images
REPOSITORY           TAG          IMAGE ID          CREATED             SIZE
docker-whale         latest       7d9495d03763      4 minutes ago       273.7 MB
docker/whalesay      latest       fb434121fc77      4 hours ago         247 MB
hello-world          latest       91c95931e552      5 weeks ago         910 B
```
Запустите ваш новый образ командой docker run docker-whale.
```
$ docker run docker-whale
```
 _________________________________________
/ "He was a modest, good-humored boy. It  \
\ was Oxford that made him insufferable." /
 -----------------------------------------
          \
           \
            \     
                          ##        .            
                    ## ## ##       ==            
                 ## ## ## ##      ===            
             /""""""""""""""""___/ ===        
        ~~~ {~~ ~~~~ ~~~ ~~~~ ~~ ~ /  ===- ~~~   
             \______ o          __/            
              \    \        __/             
                \____\______/   


Работа с docker compose

Создайте каталог для проекта:
```
$ mkdir composetest
$ cd composetest
```
В удобном для вас редакторе кода создайте файл app.py в каталоге проекта.
```
from flask import Flask
from redis import Redis

app = Flask(__name__)
redis = Redis(host='redis', port=6379)

@app.route('/')
def hello():
    redis.incr('hits')
    return 'Hello World! I have been seen %s times.' % redis.get('hits')

if __name__ == "__main__":
    app.run(host="0.0.0.0", debug=True)
  ```

В нем указаны зависимости приложения.

Шаг 2: Создание Docker образа

В этом шаге, вы создадите новый образ Docker. Образ будет содержать все зависимости приложения Python включая сам Python.
В каталоге вашего проекта создайте файл с именем Dockerfile и следующим содержанием:
```
FROM python:2.7
ADD . /code
WORKDIR /code
RUN pip install -r requirements.txt
CMD python app.py
```
Данные команды говорят Docker сделать следующее:
```
Создать образ взяв за основу образ Python 2.7.
Добавить текущую директорию . в каталог /code в образе.
Установить рабочую директорию /code.
Установить Python зависимости.
Задать команду по умолчанию для контейнера python app.py
```
Сборка образа.
```
$ docker build -t web .
```
Данная команда создает образ с названием web из содержимого текущего каталога. Команда автоматически находит Dockerfile, app.py и requirements.txt.

Шаг 3: Описание сервисов

Набор сервисов определяется с помощью файла docker-compose.yml:

Создайте файл с именем docker-compose.yml в каталоге вашего проекта и следующим содержанием:
```
version: '2'
services:
  web:
    build: .
    ports:
     - "5000:5000"
    volumes:
     - .:/code
    depends_on:
     - redis
  redis:
    image: redis
```
В Compose файле определены два сервиса, web и redis. Web сервис:

Создается из Dockerfile в текущем каталоге.
Связывает 5000 порт контейнера с 5000 портом на хосте.
Монтирует каталог проекта на хосте в каталог /code внутри контейнера позволяя вам изменять код без пересоздания контейнера.
Линкует web сервис с Redis сервисом.

Сервис redis использует последний публичный образ Redis загруженный с Docker Hub.

Шаг 4: Сборка и запуск вашего приложения в Compose

Из директории с вашим проектом запустим приложение.
```
$ docker-compose up
Pulling image redis...
Building web...
Starting composetest_redis_1...
Starting composetest_web_1...
redis_1 | [8] 02 Jan 18:43:35.576 # Server started, Redis version 2.8.3
web_1   |  * Running on http://0.0.0.0:5000/
web_1   |  * Restarting with stat
```
Compose загружает образ Redis, собирает образ для выполнения вашего кода и запускает сервисы которые вы задали.

Введите в браузере http://0.0.0.0:5000/ что бы убедиться что ваше приложение работает.

Если вы используете Docker на Linux, то веб приложение будет слушать порт 5000 на хосте где запущен Docker демон. Если адрес http://0.0.0.0:5000 не отвечает, попробуйте ввести http://localhost:5000.

Если вы используете Docker Machine на Mac, используйте команду docker-machine ip MACHINE_VM что бы получить IP адрес Docker хоста. Затем введите open http://MACHINE_VM_IP:5000 в браузере.

Вы должны увидеть следующее сообщение в браузере:

Hello World! I have been seen 1 times.

Обновите страницу.

Число должно увеличиться.
  
# web5. Реализация веб-приложения с технологий computer vision
В данной работе рассматривается возможность использования готовых сервисов, реализующий функционал computer vision.
Для реализации данной лабораторной работы требуется создать веб-приложение, позволяющее:
1.	Загрузить изображение
2.	Отобразить информацию о находящихся объектах на изображении
Выполнение работ
Распознование образов на питон
Установка 
```
pip install tensorflow==2.4.0
```
установка
```
pip install keras==2.4.3 numpy==1.19.3 pillow==7.0.0 scipy==1.4.1 h5py==2.10.0 matplotlib==3.3.2 opencv-python keras-resnet==0.2.0
pip install imageai --upgrade
```
Код
```
from imageai.Detection import ObjectDetection
import os

execution_path = os.getcwd()

detector = ObjectDetection()
detector.setModelTypeAsRetinaNet()
detector.setModelPath( os.path.join(execution_path , "resnet50_coco_best_v2.1.0.h5"))
detector.loadModel()
detections = detector.detectObjectsFromImage(input_image=os.path.join(execution_path , "image.jpg"), output_image_path=os.path.join(execution_path , "imagenew.jpg"))

for eachObject in detections:
    print(eachObject["name"] , " : " , eachObject["percentage_probability"] )
```
# web6. S3
```
//Подключение 
import boto3
session = boto3.session.Session()
s3_client = session.client(
    service_name = 's3',
    endpoint_url = 'https://hb.bizmrg.com',
    aws_access_key_id = 'ioJHUeiHLHF9b8sW2eLBsc',
    aws_secret_access_key = '5sSENeUviPoFp7c6nZ6c5GTJzguLzcezofd1tJig3dKc'
)
test_bucket_name = 'boto3-test-bucket-name'
// Создаем бакет
s3_client.create_bucket(Bucket=test_bucket_name)
response = s3_client.list_buckets()
print(response)
//Получение списка бакетов
for key in response['Buckets']:
    print(key['Name'])

    test_bucket_name = 'boto3-test-bucket-name'

    // Загрузка данных из строки
    s3_client.put_object(Body='TEST_TEXT_TEST_TEXT', Bucket=test_bucket_name, Key='test_file.txt')

    // Загрузка локального файла в бакет
    s3_client.upload_file('some_test_file_from_local.txt', test_bucket_name, 'copy_some_test_file.txt')

    // Загрузка локального файла в директорию внутри бакета
    s3_client.upload_file('some_test_file_from_local.txt', test_bucket_name, 'backup_dir/copy_some_test_file.txt')


response = s3_client.get_object(Bucket='boto3-test-bucket-name', Key='test_file.txt')
print(response)
print(response['Body'].read())


test_bucket_name = 'boto3-test-bucket-name'
//Получение списка объектов
for key in s3_client.list_objects(Bucket=test_bucket_name)['Contents']:
    print(key['Key'])
    ```
