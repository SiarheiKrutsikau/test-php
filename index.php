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
      'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
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
      'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
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
    $rand = rand(0, $rand_max);
    //переведем случайное число в md5, для получения barcode
    $barcode = md5($rand);

    //отправка api через функцию apisitecom
    $tr = apisitecom($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode);
} while ($tr!='order successfully booked');

//на стороннюю апи запрос с подтверждением (https://api.site.com/approve), через функцию apisitecomapprove
do
    {
    $tr = apisitecomapprove($barcode);
} while ($tr != 'order successfully aproved');

// подключение к базе данных и запись
if ($tr == 'order successfully aproved') {
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
        echo "К базе подключено успешно";
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


?>

