<?php
/*
    Класс создает соединение с базой данных
  */

class DataBase
{
    public static $mConnect;	// Хранит результат соединения с базой данных
    public static $mSelectDB;	// Хранит результат выбора базы данных

    // Метод создает соединение с базой данных
    public static function Connect($host, $user, $pass, $name)
    {
        // Пробуем создать соединение с базой данных
        self::$mConnect = mysql_connect($host, $user, $pass);

        // Если подключение не прошло, вывести сообщение об ошибке..
        if(!self::$mConnect)
        {
            echo "<p><b>К сожалению, не удалось подключиться к серверу MySQL</b></p>";
            exit();
            return false;
        }

        // Пробуем выбрать базу данных
        self::$mSelectDB = mysql_select_db($name, self::$mConnect);

        // Если база данных не выбрана, вывести сообщение об ошибке..
        if(!self::$mSelectDB)
        {
            echo "<p>К сожалению, не удалось подключиться к базе <b>".mysql_error()."</b></p>";
            exit();
            return false;
        }

        // Возвращаем результат
        return self::$mConnect;
    }

    // Метод закрывает соединение с базой данных
    public static function Close()
    {
        // Возвращает результат
        return mysql_close(self::$mConnect);
    }

}
?>
