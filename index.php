<?php

echo '<h1>Импорт/обновление таблицы пользователей</h1>';

// Параметры соединения с базой данных
require_once 'connector.php';

// Загружаем соединение с базой данных
require_once 'database.class.php';

// Соединение с базой данных
DataBase::Connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

//Проверяем таблицу на существование
$getTable = mysql_query ("SELECT * FROM `".DB_TABLE."` LIMIT 1");

if ( !$getTable ) { 
	echo '<p>Таблица отсутствует. Будет созданна при первом импорте</p>';
	$style = 'disabled';
} 

// Закрываем соединение с базой данных
DataBase::Close();

echo'<label>Выбор файла для первого импорта пользователей (таблица не созданна, либо пуста)</label>
	<form enctype="multipart/form-data" action="first_pars.php" method="POST">
		Отправить этот файл: <input name="file" type="file"/>
		<input type="submit" value="Импорт" />
	</form>
';

echo '<label>Обновление таблицы пользователей</label>
	<form enctype="multipart/form-data" action="update_table.php" method="POST">
		<label>Email для отправки отчета</label>
		<input type="text" name="email" value="" placeholder="Введите email" '.$style.' "></br>
		Отправить этот файл: <input name="file" type="file" '.$style.'/>
		<input type="submit" value="Обновить" '.$style.'/>
	</form>
';
?>
