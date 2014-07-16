<?php
//Засекаем время выполнения скрипта
$start = microtime(true); 

echo '<h1>Импорт таблицы пользователей</h1>';

//Проверяем выбран ли файл
if ( empty( $_FILES['file']['size'] ))
	{
        echo "<p>Не выбран файл импорта</p>";
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
if ( $_FILES["file"]["type"] != 'text/xml')	
	{
        echo "<p>Формат файла импорта не верен</p>";
        exit();
    }

//Проводим валидацию загруженного файла
$dom = new DOMDocument;
$dom->validateOnParse = true;
$dom->Load($_FILES['file']['name']);
	
// Параметры соединения с базой данных
require_once 'connector.php';

// Загружаем соединение с базой данных
require_once 'database.class.php';

// Соединение с базой данных
DataBase::Connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Проверяем существование таблицы, в случае отсутствия создаем
$create_table = mysql_query("CREATE TABLE IF NOT EXISTS `".DB_TABLE."`(`id_user` int (10) AUTO_INCREMENT, `login` varchar(20) NOT NULL, `password` varchar(15) NOT NULL, `username` varchar(20) NOT NULL, `email` varchar(50) NOT NULL, PRIMARY KEY(`id_user`))");

//Если таблицу на удалось создать то выводим сообщение об ошибке
if (!$create_table)
	{
        echo "<p>К сожалению, не удалось создать таблицу <b>".mysql_error()."</b></p>";
        exit();
    }
	
	// Получаем корневой элемент
	$root = $dom->documentElement;
	
	// Получаем дочерние элементы у корневого элемента
	$childs = $root->childNodes;

	//Количество импортируемых елементов
	$count = $childs->length;

	// Перебираем полученные элементы
	for ($i = 0; $i < $childs->length; $i++) {
		$user = $childs->item($i);
		$lp = $user->childNodes; // Получаем дочерние элементы у узла "user"
		$login = $lp->item(0)->nodeValue; // Получаем значение узла "login"
		$password = $lp->item(1)->nodeValue;// Получаем значение узла "password"
		
		//Проверяем узлы на заполненность, если узел пустой то вывдим сообщение об ошибке и прекращаем импорт
		if ($login == '' || $password == ''){
			$i++;
			echo "<p>Импорт товаров остановлен, ошибка в строке $i - узел login или узел password имеет пустое значение </p>";
			exit();
		}
		
		//Сравниваем значение узла login из файла импорта и таблицы, на случай ошибки в импорте
		//в случае не совпадения заносим в базу
		$getLogin = mysql_query("SELECT `login` FROM `".DB_TABLE."` WHERE `login`='".$login."' LIMIT 1");
			while($row = mysql_fetch_array($getLogin)){
				$log =  $row["login"];
			}
		if ($login != $log)
		{
			$import_table = mysql_query("INSERT INTO `".DB_TABLE."` (`login`, `password`, `username`, `email`) VALUES ('".$login."', '".$password."', '".$login."', '".$login."@example.com')");
			
			//Проверяем добавилась ли запись
			if (!$import_table)
			{
				echo "<p>К сожалению, не удалось завершить импорт <b>".mysql_error()."</b></p>";
				exit();
			}
		} 
	}
	
	// Закрываем соединение с базой данных
	DataBase::Close();

	//Выводим время выполенения скрипта и количество обработанных строк
	echo "Время выполнения скрипта: ".(microtime(true) - $start)." сек. </br>Импортированно строк - $count";
