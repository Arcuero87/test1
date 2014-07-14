<?php
echo '<form enctype="multipart/form-data" action="update_table.php" method="POST">
	<input type="text" name="email" value="" placeholder="Введите email"></br>
    Отправить этот файл: <input name="file" type="file" />
    <input type="submit" value="Обновить" />
</form>';
