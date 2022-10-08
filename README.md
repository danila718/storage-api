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
