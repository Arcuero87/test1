<?php
$start = microtime(true); 

$xml = simplexml_load_file('users.xml');

$db = mysql_connect("localhost", "root", ""); 

if(! $db )
  die('Could not connect - server: ' . mysql_error());

if (! mysql_select_db("test" ,$db) ){
	die('Could not connect - db: ' . mysql_error());
} else {
	$count = count($xml->user);
	foreach ($xml->user as $item) {
		mysql_query("INSERT INTO `test1` (`login`, `password`, `username`, `email`) VALUES ('".$item->login."', '".$item->password."', '".$item->login."', '".$item->login."@example.com')") or die("Ooops, error : ".mysql_error());
	}
}
mysql_close();
  echo "Время выполнения скрипта: ".(microtime(true) - $start)." сек. </br>Импортированно строк - $count";
