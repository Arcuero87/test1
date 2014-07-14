<?php 
$start = microtime(true); //засекаем время выполениния скрипта

//загрузка файла на сервер
$uploaddir = 'uploads/';
$file = basename($_FILES['file']['name']);
$uploadfile = $uploaddir . $file;
move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

$type = end(explode(".", $_FILES['file']['name']));

//подключение к базе
$db = mysql_connect("localhost", "root", ""); 

if(! $db )
  die('Could not connect - server: ' . mysql_error());

if (! mysql_select_db("test" ,$db) )
	die('Could not connect - db: ' . mysql_error());

//парс файла в зависимости от его расширения и обновление таблицы	
if ($type == 'xml'){
	$xml = simplexml_load_file($uploaddir . $file);

	$upd = 0;
	$count = count($xml->user);

	foreach ($xml->user as $item) {
		$query = sprintf("SELECT username, email FROM test1 WHERE login='".$item->login."'",
			mysql_real_escape_string($username),
			mysql_real_escape_string($email)); 
			$result = mysql_query($query);
			$result = mysql_fetch_assoc($result);
			if ($item->username != $result['username'] || $item->email != $result['email']){
				mysql_query ("UPDATE test1 SET username='$item->username', email='$item->email' WHERE login='$item->login'") or die(mysql_error());
				$upd++;
			}	
		$login_arr[] = 	$item->login;
	}
} else {

	$a = array(); 
	$fp = fopen($uploaddir . $file, "r"); 
	while (!feof($fp)) { 
		 $a[] = fgetcsv($fp, 1024, ";"); 
	}
	$count = count($a);
	foreach ($a as $qwe){
		$query = sprintf("SELECT username, email FROM test1 WHERE login='".$qwe[0]."'",
			mysql_real_escape_string($username),
			mysql_real_escape_string($email)); 
			$result = mysql_query($query);
			$result = mysql_fetch_assoc($result);
			if ($qwe[2] != $result['username'] || $qwe[3] != $result['email']){
				mysql_query ("UPDATE test1 SET username='$qwe[2]', email='$qwe[3]' WHERE login='$qwe[0]'") or die(mysql_error());
				$upd++;
			}	
		$login_arr[] = 	$qwe[0];
	}
}	
	
	//Пользователей, отсутствующих в файле, удаляем из БД.
	$sql = "SELECT login FROM test1"; 
	$tmp = mysql_query($sql); 
	while($tit_o = mysql_fetch_array ($tmp))  { 
	   $array[] = $tit_o['login']; 
	}  
	
	function key_compare_func($key1, $key2)
	{
		if ($key1 == $key2)
			return 0;
		else if ($key1 > $key2)
			return 1;
		else
			return -1;
	}
	$del_item = 0;
	$result = array_diff_ukey($array, $login_arr, 'key_compare_func');
	foreach ($result as $del){
		$sql = mysql_query ("DELETE FROM test1 WHERE login='".$del."'");
		$del_item++;
	}	
	
	mysql_close();

//отправляем отчет на указанный email
$to  = "permyakov87@gmail.com"; //почта для отправки отчета

$subject = "Отчет о обновлении списка пользователей"; 

$message = ' 
<html> 
    <head> 
        <title>Отчет о обновлении списка пользователей</title> 
    </head> 
    <body> 
        <p>Всего строк - '.$count.'</p>
		<p>Обновленно строк - '.$upd.'</p>	
		<p>Удалено строк - '.$del_item.'</p>
    </body> 
</html>'; 

$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
$headers .= "From: <".$to.">\r\n"; 
//если почта отправленна выводим отчет в окно браузера
if (mail($to, $subject, $message, $headers))
  echo "<p>Время выполнения скрипта: ".(microtime(true) - $start)." сек. </p><p>Обновленно строк - $upd </p><p>Обработанно строк - $count</p><p> Удалено строк - $del_item </p>";
