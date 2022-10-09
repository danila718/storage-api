# Storage API
Сервис для хранения файлов. Взаимодействия с сервисом через API интерфейс.

Функционал:
1. Регистрация ``POST /register``
2. Аутентификация (выдается токен) ``POST /login``
3. Информация о пользователе ``GET /user/info``
4. Загрузка файла (можно указать id папки) ``POST /file``
5. Переименование файла: ``PATCH /file/{id}``
6. Удаление файла ``DELETE /file/{id}``
7. Скачивание файла ``GET /file/{id}`` 
8. Создание общедоступной ссылки на файл ``POST /file/share/{id}`` 
9. Удаление общедоступной ссылки на файл ``DELETE /file/share/{id}`` 
10. Создание папки ``POST /folder``
11. Получение размера всех файлов (если указать id папки, то вернется размер файлов внутри) ``GET /storage/total-size``
12. Список папок и файлов (если указать id папки, выведет список файлов внутри папки) ``GET /storage``
13. Скачивание файла по общедоступной ссылке ``GET /download/{share_id}``

Ограничения:
1. Запрещено загружать/переименовывать файлы с ``.php`` в названии
2. Максимальный размер загружаемого файла ``20 MB``, можно изменить в ``config/services.php`` параметр ``maxFileSize``
3. Максимальный размер хранилища на пользователя ``100 MB``, можно изменить в ``config/services.php`` параметр ``totalUserSpace``

# Установка

1. клонируем репозиторий ``git clone git@github.com:danila718/storage-api.git``
2. из папки с проектом запустить установку зависимостей Composer командой ниже
    ```shell
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v $(pwd):/var/www/html \
        -w /var/www/html \
        laravelsail/php81-composer:latest \
        composer install --ignore-platform-reqs
    ```
3. создаем .env файл ``cp .env.example .env``
4. запускаем контейнеры ``vendor/bin/sail up -d``
5. генерируем APP_KEY ``vendor/bin/sail artisan key:generate``
6. запускаем миграции ``vendor/bin/sail artisan migrate``
