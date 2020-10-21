<?php
include 'vendor/autoload.php';
include 'token.php'; //Для примера. В этом файле хранится только токен

use sancho2804\rest_client\main; //Используем namespace 

//Создаем объект класса rest_client со ссылкой REST API. Если требуется атворизация по OAuth, то передаем логин OAuth и токен
$rest=new main('https://cloud-api.yandex.net:443/v1/','OAuth',$token);
//Устанавливаем имя сервиса по которому будет считан нужный json с параметрами
$rest->set_service('yandex_disk'); 

//По алиасу обращаемся REST API методу.
//2 аргумент - массив который будет передан в POST.+
//3 и далее - агументы для выполнения алиаса
$result=$rest->exec('dir_info',null,'/');

//Выводим результат
var_dump($result);
//Выводим полученные заголовки
var_dump($rest->last_request_info);



//Аналогично но более удобно можно вызвать метод сервиса как метод класса.
//Первым аргументом передается массив который будет отправлен через POST
//Затем передаются аргументы для выполнения запроса

$result=$rest->dir_info(null, '/');
//Выводим результат
var_dump($result);
//Выводим полученные заголовки
var_dump($rest->last_request_info);
