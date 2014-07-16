<?php 
//засекаем время выполениния скрипта
$start = microtime(true); 

echo '<h1>Обновление таблицы пользователей</h1>';

//Проверяем введен ли email и корректен ли он
if (!checkEmail($_POST['email']))
	{
		echo 'Введите корректный email адрес';
	}
	
function checkEmail($email) {
    if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email)){
        list($username,$domain)=explode('@',$email);
            if(!checkdnsrr($domain,'MX')) {
                return false;
            }
        return true;
        }
    return false;
}

//Проверяем выбран ли файл
if ( empty( $_FILES['file']['size'] ))
	{
        echo "<p>Не выбран файл обновления</p>";
        exit();
    }

//Проверяем расширение файла	
$blacklist = array(".php", ".phtml", ".php3", ".php4", ".html", ".htm");
  foreach ($blacklist as $item)
    if(preg_match("/$item\$/i", $_FILES['file']['name'])){
        echo "<p>Выбран файл запрещенного формата</p>";
        exit();
    }
	
//Проверяем тип файла	
if ( $_FILES["file"]["type"] != 'text/xml' && $_FILES["file"]["type"] != 'application/vnd.ms-excel' && $_FILES["file"]["type"] != 'text/plain')	
	{
        echo "<p>Формат файла обновления не верен</p>";
        exit();
    }
    
//Проверяем наличие папки для загрузки,в случае отсутствия создаем
if (!is_dir("uploads"))
{
   mkdir("uploads", 0700);
}     

//загрузка файла на сервер
$uploaddir = 'uploads/';
$file = basename($_FILES['file']['name']);
$uploadfile = $uploaddir . $file;
move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

// Параметры соединения с базой данных
require_once 'connector.php';

// Загружаем соединение с базой данных
require_once 'database.class.php';

// Соединение с базой данных
DataBase::Connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

//Смотрим файл формата xml или csv
if ($_FILES["file"]["type"] == 'text/xml')
	{
		//Проводим валидацию загруженного файла
		$dom = new DOMDocument;
		$dom->validateOnParse = true;
		$dom->Load($uploadfile);

		// Получаем корневой элемент
		$root = $dom->documentElement;
		
		// Получаем дочерние элементы у корневого элемента
		$childs = $root->childNodes;

		//Количество импортируемых елементов
		$count = $childs->length;

		//устанавливаем переменную для подсчета обновленных файлов
		$upd = 0;
		
		// Перебираем полученные элементы
		for ($i = 0; $i < $childs->length; $i++) 
			{
				$user = $childs->item($i);
				$lp = $user->childNodes; // Получаем дочерние элементы у узла "user"
				$login = $lp->item(0)->nodeValue; // Получаем значение узла "login"
				$password = $lp->item(1)->nodeValue;// Получаем значение узла "password"
				$username = $lp->item(2)->nodeValue;// Получаем значение узла "username"
				$email = $lp->item(3)->nodeValue;// Получаем значение узла "email"

				//получаем строку для обновления
				$query = sprintf("SELECT username, email FROM ".DB_TABLE." WHERE login='".$login."'",
				mysql_real_escape_string($username),
				mysql_real_escape_string($email)); 
				$result = mysql_query($query);
				$result = mysql_fetch_assoc($result);

				//если изменились email или имя пользователя, то обновляем запись
				if ($username != $result['username'] || $email != $result['email']){
					$update = mysql_query ("UPDATE ".DB_TABLE." SET username='$username', email='$email' WHERE login='$login'");

					//соообщаем об ошибке если обновление не удалось
					if (!$update){
						echo "Обновление таблицы закончилось неудачно - ".mysql_error();
						exit;
					} else {
						//увеличиваем счетчик удачно обновленных записей
						$upd++;
					}
				}	
				
				//записываем в массив login пользователя
				$login_arr[] = 	$login;
			}
	} else {

			$a = array(); 
			//проходим по файлу 
			$fp = fopen($uploaddir . $file, "r"); 
			while (!feof($fp)) { 
				 $a[] = fgetcsv($fp, 1024, ";"); 
			}
			
			//подсчитываем количество элементов в файле
			$count = count($a);
			
			//проходим по массиву собранному из файла
			foreach ($a as $qwe){
				
				//Проверяем поля на заполненность
				if ($qwe[0] == '' || $qwe[2] == '' || $qwe[3] == ''){
					echo 'Ошибка при обновлении записи';
					exit();
				}
					
				$query = sprintf("SELECT username, email FROM ".DB_TABLE." WHERE login='".$qwe[0]."'",
					mysql_real_escape_string($username),
					mysql_real_escape_string($email)); 
					$result = mysql_query($query);
					$result = mysql_fetch_assoc($result);
					
					//Если данные из файла отличаются от записи из базы, то обновляем запись
					if ($qwe[2] != $result['username'] || $qwe[3] != $result['email']){
						$update = mysql_query ("UPDATE ".DB_TABLE." SET username='$qwe[2]', email='$qwe[3]' WHERE login='$qwe[0]'");
						
						//соообщаем об ошибке если обновление не удалось
						if (!$update){
							echo "Обновление таблицы закончилось неудачно - ".mysql_error();
							exit;
						} else {
							//увеличиваем счетчик удачно обновленных записей
							$upd++;
						}
					}	
					
				//записываем в массив login пользователя	
				$login_arr[] = 	$qwe[0];
			}
	}	
	
//удаление пользователей отсутствующих в файле	
//выбираем логины пользователей из базы
$sql = "SELECT login FROM ".DB_TABLE; 
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

//переменная для подсчета количества удаленных записей
$del_item = 0;

//Пользователей, отсутствующих в файле, удаляем из БД.
$result = array_diff_ukey($array, $login_arr, 'key_compare_func');
foreach ($result as $del){
	$sql = mysql_query ("DELETE FROM ".DB_TABLE." WHERE login='".$del."'");
	
	//при удалении увеличиваем счетчик
	$del_item++;
}	

// Закрываем соединение с базой данных
DataBase::Close();

//отправляем отчет на указанный в форме email
$to  = $_POST['email']; 

$subject = "Отчет о обновлении списка пользователей"; 

$message = " 
<html> 
    <head> 
        <title>Отчет о обновлении списка пользователей</title> 
    </head> 
    <body> 
        <p>Всего строк - $count</p>
		<p>Обновленно строк - $upd</p>	
		<p>Удалено строк - $del_item</p>
    </body> 
</html>"; 

$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
$headers .= "From: <".$to.">\r\n"; 

//если почта отправленна выводим отчет в окно браузера
if (mail($to, $subject, $message, $headers))
  echo "<p>Время выполнения скрипта: ".(microtime(true) - $start)." сек. </p><p>Обновленно строк - $upd </p><p>Обработанно строк - $count</p><p> Удалено строк - $del_item </p>";
