<?php
// include 'class.php'; //Подключаем файл с классом
include 'vendor/autoload.php';
include 'token.php'; //Для примера. В этом файле хранится только токен

use sancho2804\rest_client\main; //Используем namespace 

//Создаем объект класса rest_client со ссылкой REST API. Если требуется атворизация по OAuth, то в логин передаем OAuth, а в пароль токен
$rest=new main('https://cloud-api.yandex.net:443/v1/','OAuth',$token);

$result=$rest->get('disk/resources?path=/');//Получаем информацию о директории
var_dump($result);//Выводим результат
echo '<hr>';
var_dump($rest->last_request_info);//Выводим заголовки

//========================================================================================
//========================================================================================
//========================================================================================

$rest->eat_json('json/yandex_disk.json');//Скармливаем подготовленный JSON
$result=$rest->exec('dir_info',null,true,'/');//По алиасу обращаемся REST API методу, указывая только путь 4-ым параметром

var_dump($result);//Выводим результат
echo '<hr>';
var_dump($rest->last_request_info);//Выводим заголовки
