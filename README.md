# Клиент для REST API на PHP
Многие сервисы с которыми приходится иметь дело, имеют на борту REST API. Это очень удобно для разработчиков. Для работы с апи необходимо разрабатывать модуль для решения конкретных задач. Я предлагаю упростить задачу используя **"карты REST API сервисов"**. Такие карты оформляются в формате **JSON**. Но класс можно использовать и для обращения к методам сервиса и напрямую.
___
## Стартуем ~~, я начну стрелять~~:
**Устанавливаем через composer:**
```cmd
composer require sancho2804/rest_client
```
     
Подключаем файл с классом и создаем объект класса:
```php
include_once 'vendor/autoload.php';
use sancho2804\rest_client\init; //Используем namespace 
$yandex_disk=new init('https://cloud-api.yandex.net:443/v1/','OAuth',$token);
```

**Скачиваем и подключаем файл с классом:**
```php
include_once 'class.php';
use sancho2804\rest_client\init; //Используем namespace 
$yandex_disk=new init('https://cloud-api.yandex.net:443/v1/','OAuth',$token);
```
Первый параметр ссылка на REST API нужного вам сервиса. Именно к этой ссылке будут добавляться указываемые в дальнейшем пути (сокращает кол-во повторений). Если авторизация для сервиса не требуется, то 2 и 3 параметр можно опустить.
___
## Доступные методы:

### rest_client::get($path, $object, $parse_json)
Делает запрос к методу сервиса по HTTP методу GET. 

Аргументы:
1. **$path** - относительный путь до метода
2. **$object** - по умолчанию null. Если передать массив, то он передастся в POST заголовок
3. **$parse_json** - по умолчанию true. Указывает на то, что результат с сервиса приходит в формате JSON и его необходимо распарсить

**Возвращаемы значения:** по умолчанию - результат ответа в формате массива. Если $parse_json установлен в false, то сырой результат. 
```php
$yandex_disk->get('disk/resources?path=/');
//или для получения сырой строки
$yandex_disk->get('disk/resources?path=/',null,false);
```

### rest_client::save($path, $object, $parse_json)
Делает запрос к методу сервиса по HTTP методу POST. 

Аргументы: **такие же как и в методе get**

**Возвращаемы значения: такие же как и в методе get** 
```php
$yandex_disk->save('disk/resources/copy?from=/file1.php&path=/file2.php');
//или для получения сырой строки
$yandex_disk->save('disk/resources/copy?from=/file1.php&path=/file2.php',null,false);
```

### rest_client::delete($path, $object, $parse_json)
Делает запрос к методу сервиса по HTTP методу DELETE. 

Аргументы: **такие же как и в методе get**

**Возвращаемы значения: такие же как и в методе get** 
```php
$yandex_disk->delete('disk/resources?path=/file2.php');
//или для получения сырой строки
$yandex_disk->delete('disk/resources?path=/file2.php',null,false);
```

### rest_client::update($path, $object, $parse_json)
Делает запрос к методу сервиса по HTTP методу PATCH. 

Аргументы: **такие же как и в методе get**

**Возвращаемы значения: такие же как и в методе get** 
```php
$yandex_disk->update('disk/resources?path=/file2.php&body=...');
//или для получения сырой строки
$yandex_disk->update('disk/resources?path=/file2.php&body=...',null,false);
```

### rest_client::create($path, $object, $parse_json)
Делает запрос к методу сервиса по HTTP методу PUT. 

Аргументы: **такие же как и в методе get**

**Возвращаемы значения: такие же как и в методе get** 
```php
$yandex_disk->create('disk/resources/publish?path=/file2.php');
//или для получения сырой строки
$yandex_disk->create('disk/resources/publish?path=/file2.php',null,false);
```

### rest_client::options($path, $object, $parse_json)
Делает запрос к методу сервиса по HTTP методу OPTIONS. 

Аргументы: **такие же как и в методе get**

**Возвращаемы значения: такие же как и в методе get** 
```php
//Пока не встречал его в работе, но он как суслик
```

### rest_client::eat_json($path)
Съедает JSON-карту отформатированную определенным образом. 

Аргументы:
1. путь до файла JSON

**Возвращаемые значения: void** 
```php
$yandex_disk->eat_json('json/yandex_disk.json');
```

### rest_client::exec($alias, $object, $parse_json, [$arg1, ....])
Вызывает определенный метод сервиса на основе скормленной карты. Один раз составил карту и пользуешся.  

Аргументы:
1. **$alias** - алиас по которому вызывается метод сервиса
2. **$object** - по умолчанию null. Если передать массив, то он передастся в POST заголовок
3. **$parse_json** - по умолчанию true. Указывает на то, что результат с сервиса приходит в формате JSON и его необходимо распарсить
4. **любое кол-во аргументов необходимых для выполнения запроса к методу сервиса**

**Возвращаемые значения:** по умолчанию - результат ответа в формате массива. Если $parse_json установлен в false, то сырой результат. 
```php
$yandex_disk->exec('dir_info',null,true,'/');
```
___
## Формат JSON-карты:
```json
{
    "dir_info":{
        "method":"GET",
        "uri":"disk/resources?path={::}&offset={::}&limit={::}&sort={::}&fields={::}",
        "require":[0]
    },
    ...
}
```
+ **dir_info** - алиас по которому происходит обращение к сервису
+ **method** - метод HTTP. GET, POST, DELETE, PATCH, PUT, OPTIONS
+ **uri** - путь до метода REST API. Конструкции вида {::} будут заменены на аргументы из метода rest_client::exec в том порядке, в котором они передаются
+ **require** - Указываются номера конструкции {::}, которые нужно обязательно передать. **Отсчет идет с 0**
