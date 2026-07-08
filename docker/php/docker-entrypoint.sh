#!/bin/sh
set -e

# Проверяем, пуста ли папка app. Если в ней ничего нет, ставим Symfony
if [ ! -f app/public/index.php ]; then
    echo "Инициализация Symfony 7 в папке app..."
    composer create-project symfony/skeleton:^7.0 app --no-interaction
else
    echo "Symfony 7 уже установлена, пропускаем инициализацию."
fi

# Запускаем основной процесс PHP-FPM
exec php-fpm
