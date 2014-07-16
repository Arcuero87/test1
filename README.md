test1
=====

Тестовое задание

Настройки подключения к базе лежат в файле connector.php

При первом импорте таблица будет создана автоматически.

Файлы для проверки: users.xml - файл для первого импорта (был предоставлен Вами)

                    new_users.xml - файл для теста обновления через xml
                    
                    new_users.csv - файл для теста обновления через csv

Дан xml-файл со списком пользователей и хэшей их паролей.
1. Создать БД с таблицей (логин, пароль, имя, е-мейл).
2. Используя php, импортировать пользователей из файла, подставляя в поле е-мейл данные вида логин@example.com, а в поле имя — логин.
3. Написать скрипт обновления данных в данной таблице.
3.1 Через форму загружаем файл с обновлёнными данными пользователей (xml или csv).
3.2 Обновляем данные пользователей в базе в соответствии с загруженным файлом (логин постоянный, могут изменяться е-мейл, имя).
3.3 Пользователей, отсутствующих в файле, удаляем из БД.
3.4 На почту, указанную в настройках, отсылаем отчёт по обновлению: сколько записей обработано, сколько обновлено, сколько удалено.
3.5 Выводим этот же отчёт в браузер.
Результат надо предоставить в виде ссылки на git-репозиторий (github, bitbucket, ...)


— Дамп структуры БД или автоматическое создание таблицы при первом импорте (индексы, create/update date)
— Валидацию загруженных файлов, проверку на наличие/отсутствие нужных полей
— Корректную обработку ошибок в скриптах и sql-запросах
— Чтобы e-mail из формы учитывался при отправке отчёта
— Примеры файлов для проверки работоспособности
