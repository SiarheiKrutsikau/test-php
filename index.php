<?php
echo "Решение задачь в самом тексте PHP файла. Задача 1 и задача 2"; 

//задача 2  в конце

/* **Задача1: написать функцию, которая будет добавлять заказы в  таблицу.***/
/* Добавляются заказы с помощью MySQL запроса после проверок*/

// функция для API (api.site.com) броня
function apisitecom($event_id2, $event_date2, $ticket_adult_price2, $ticket_adult_quantity2, $ticket_kid_price2,$ticket_kid_quantity2, $barcode) {
  $query_data = array(
    'client' => 'x',
    'q' => $event_id2,
    'hl'=>$event_date2,
    'sl' => $ticket_adult_price2,
    'sl2' => $ticket_adult_quantity2,
    'sl3' => $ticket_kid_price2,
    'sl4' => $ticket_kid_quantity2,
    'tl' => $barcode
  );
  $filename = 'https://api.site.com/book';
  $options = array(
    'http' => array(
      'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67',
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query($query_data)
    )
  );
  $context = stream_context_create($options);
  $response = file_get_contents($filename, false, $context);
  
  return json_decode($response);
}


// функция для API запрос с подтверждением (https://api.site.com/approve)  
// мало опыта  с API, предположительно
function apisitecomapprove($barcode) {
  $query_data = array(
    'client' => 'x',
    'tl' => $barcode
  );
  $filename = 'https://api.site.com/approve';
    $options = array(
        'http' => array(
      'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67',
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query($query_data)
    )
  );
  $context = stream_context_create($options);
    $response = file_get_contents($filename, false, $context);

    return json_decode($response);
}

$event_id;
$event_date;
$ticket_adult_price;
$ticket_adult_quantity;
$ticket_kid_price;
$ticket_kid_quantity;

//преобразуем их к числовому виду (int)
$event_id1=(int) $event_id;
$event_date1=(int)$event_date;
$ticket_adult_price1=(int)$ticket_adult_price;
$ticket_adult_quantity1=(int)$ticket_adult_quantity;
$ticket_kid_price1=(int)$ticket_kid_price;
$ticket_kid_quantity1=(int)$ticket_kid_quantity;

//сгенерировать `barcode` c помощью случайного числа переведенного в md5
//максимальная сумма (для уникальности)
$rand_max=$event_id1+$event_date1+$ticket_adult_price1+$ticket_adult_quantity1+$ticket_kid_price1+$ticket_kid_quantity1;

do 
{
//сгенерируем случайное число для получения barcode, который не должен повторяться
$rand=rand(0,$rand_max);
//переведем случайное число в md5, для получения barcode
$barcode= md5($rand);

//отправка api через функцию apisitecom
$tr=apisitecom($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity,$barcode);
}
while ($tr!='order successfully booked');

//на стороннюю апи запрос с подтверждением (https://api.site.com/approve), через функцию apisitecomapprove
do
{
    $tr1=apisitecomapprove($barcode);
}
while ($tr1!='order successfully aproved');

// подключение к базе данных и запись
if ($tr1=='order successfully aproved')
{
    $host="localhost";
    $user="b9";
    $password="12345";
    $database="base";
    ini_set('display_errors', 0);
    //подключаемся к серверу
    $connect = mysqli_connect($host, $user, $password, $database);

    if ($connect == false) {
        echo "<div style='color:red'>Не возможно соединиться с базой данных. Подключите базу ...  </div>" ; 
        }
    else
        {
        ini_set('display_errors', 1);
        //echo "К базе подключено успешно"; 
        }

    //общая сумма заказов
  $equal_price = $ticket_adult_price*$ticket_adult_quantity+$ticket_kid_price*$ticket_kid_quantity;
  //дата заказа
  if ($created == false) {
      $created=date('d-m-Y H:i:s');
  }
 // запись в БД
   do
   {
    $team="INSERT INTO table (`event_id`,`event_date`, `ticket_adult_price`, `ticket_adult_quantity`,`ticket_kid_price`, `ticket_kid_quantity`, `barcode`, `equal_price`, `created`) VALUES ($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $equal_price, $created)";	
    mysqli_query($connect, $team);
    }
   while(!$ok);
 // закрытие соединения с БД
   mysql_close($connect);
}

/*### Задание №2 А ###
event_id (уникальный ид события)записывать номер, обозначающий льготный или групповой билет, можно с
обозначением скидки, а в общую суммму заказов, сумму с учетом скидки.

id  | event_id  | event_date          | ticket_adult_price  | ticket_adult_quantity  | ticket_kid_price  | ticket_kid_quantity  | barcode   | user_id  | equal_price  | created
--- | --------- | ------------------- | ------------------- | ---------------------- | ----------------- | -------------------- | --------  | -------- | ------------ | -------------------
1   | 003       | 2021-08-21 13:00:00 | 700                 | 1                      | 450               | 0                    | 11111111  | 00451    | 700          | 2021-01-11 13:22:09
2   | 006       | 2021-07-29 18:00:00 | 1000                | 0                      | 800               | 2                    | 22222222  | 00364    | 1600         | 2021-01-12 16:62:08
3   | 003       | 2021-08-15 17:00:00 | 700                 | 4                      | 450               | 3                    | 33333333  | 00015    | 4150         | 2021-01-13 10:08:45
4   | 003_1_10  | 2021-08-15 17:00:00 | 700                 | 4                      | 450               | 3                    | 33333333  | 00015    | 3735         | 2021-01-13 10:08:45
5   | 003_2_20  | 2021-08-15 17:00:00 | 700                 | 4                      | 450               | 3                    | 33333333  | 00015    | 3320         | 2021-01-13 10:08:45

В 4 строке event_id 1 - обозначает, групповой заказ с 10% скидкой, equal_price уменьшилось на 10%
В 5 строке event_id 2 - обозначает, льготный заказ с 20% скидкой, equal_price уменьшилось на 20%
Либо придумать какой-то уникальный номер 003110 для 4 строки или 003220 для 5 строки, обозначающий льготный
или групповой билет и размер скидки
всегда можно узнать общую сумму без скидок, зная проценты прописаные в event_id 
 
/*### Задание №2 Б ###
barcode поле содержит 120символов.
придумать разделитель напимер . (точку или другой). 
обозначающий конец очередного уникального штриха(кода)
при анализе скроки можно по разделителям определить количество штрихов
отсутствие barcode в билете, как .. две точки подрят
Напимер для 9 билетов
11111111.22222222.33333333.44444444.55555555.66666666.77777777.88888888.99999999.
с отсутствием barcode
11111111..33333333.44444444.55555555.66666666.77777777.88888888.99999999.
можно ввести D-детский билет, V-взрослый, L-льготный, G-групповой
V11111111.V22222222.D33333333.D44444444.D55555555.L66666666.L77777777.L88888888.L99999999.
Потом при считывании из базы данных barcode, можно разбить строку по точкам и определить
количество баркодов, по первому символу тип, по 8 символам уникальный номер
Либо тип билета закодировать в самом номере баркода

id  | event_id  | event_date          | ticket_adult_price  | ticket_adult_quantity  | ticket_kid_price  | ticket_kid_quantity  | barcode                                                                           | user_id  | equal_price  | created
--- | --------- | ------------------- | ------------------- | ---------------------- | ----------------- | -------------------- | ------------------------------------------------------------------------------    | -------- | ------------ | -------------------
1   | 003       | 2021-08-21 13:00:00 | 700                 | 1                      | 450               | 0                    | 11111111                                                                          | 00451    | 700          | 2021-01-11 13:22:09
2   | 006       | 2021-07-29 18:00:00 | 1000                | 0                      | 800               | 2                    | 22222222                                                                          | 00364    | 1600         | 2021-01-12 16:62:08
3   | 003       | 2021-08-15 17:00:00 | 700                 | 4                      | 450               | 3                    | 33333333                                                                          | 00015    | 4150         | 2021-01-13 10:08:45
4   | 003       | 2021-08-15 17:00:00 | 700                 | 4                      | 450               | 3                    | 11111111.22222222.33333333.44444444.55555555.66666666.77777777.88888888.99999999. | 00015    | 3735         | 2021-01-13 10:08:45

 */
?>

