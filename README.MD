# Symfony 7 Microservice
Тестовый микросервис на базе фреймворка Symfony 7


## Структура проекта
* `app/` — исходный код приложения Symfony 7.
* `docker/` — конфигурационные файлы инфраструктуры (PHP-FPM, Nginx, скрипты инициализации).
* `docker-compose.yml` — конфигурация Docker Compose для локальной разработки.


## Быстрый старт (для разработчиков)
Выполните следующие шаги для развертывания проекта на новом компьютере:


### 1. Клонирование репозитория
```bash
git clone <....> (TODO)
cd symfony-microservice
```

## 1.1 Создать .env.local и заполнить env-параметры. Например:
```ini
MICROSERVICE_AUTH_TOKEN=secret-microservice-token
MICROSERVICE_LOGIN=RomanSh
MICROSERVICE_PASSWORD=yepJustATest
```


### 2. Сборка и запуск контейнеров
Выполните команду сборки в корневом каталоге проекта:
```bash
docker compose up -d --build
```
При первом запуске Docker автоматически соберет образы, настроит сеть и запустит скрипт инициализации. Скрипт проверит наличие кода в папке `app/` и, если проект разворачивается с нуля, автоматически скачает ядро Symfony 7 через Composer. Этот процесс может занять 15–30 секунд.

### 2.1 Остановка проекта
Чтобы остановить работу контейнеров без потери данных:
```bash
docker compose down
```
### 2.2 Пересборка, если чего
```bash
docker compose down && docker compose up -d
```
Или форсированная версия
```bash
docker compose build --no-cache && docker compose up -d --force-recreate
```

### 3. Проверка работы
После успешного запуска микросервис будет доступен на хост-машине по адресу:
http://localhost:9090


### 4. PHP-Stan, Test, CacheReset e.t.c sripts
#### PhpStan
```bash
docker compose exec php composer stan -d app
```
Магия зашита в composer.json, блок scripts. Конфиг именно который dist, что создался "рецептом" и "сломал мне моск".

#### Tests
```bash
docker compose exec php composer test -d app
```
Потребовалось прочесть notices. Делается так:
```bash
docker compose exec php app/vendor/bin/phpunit -c app/phpunit.dist.xml --display-phpunit-notices --display-notices
```

#### ClearCache
```bash
docker compose exec php composer cc -d app
```


---
## Работа с консолью Symfony внутри Docker

Я рассчитываю, что PHP установлен только внутри контейнера, все консольные команды Symfony (`bin/console`) и пакетного менеджера (`composer`) необходимо выполнять через Docker Compose.

Выполняйте команды из корневой папки проекта, где находится файл `docker-compose.yml`.

### Основные команды разработки:

* **Просмотр доступных команд Symfony:**
  ```bash
  docker compose exec php php app/bin/console
  ```

* **Установка новых пакетов (например, MakerBundle для генерации кода):**
  ```bash
  docker compose exec php composer require --dev symfony/maker-bundle --working-dir=app
  ```

* **Создание новой консольной команды:**
  ```bash
  docker compose exec php php app/bin/console make:command AppMyCommand
  ```

* **Просмотр установленной версии Symfony и окружения:**
  ```bash
  docker compose exec php php app/bin/console --version
  ```
  
  * **Сброс кеша фрейворка:**
  ```bash
  docker compose exec php php app/bin/console cache:clear
  ```
  
---

# Permissions!

## Управление правами и вход в контейнер
В данном проекте реализовано автоматическое выравнивание идентификаторов пользователей (UID/GID). Контейнер подстраивается под вашего локального пользователя на Linux.
Благодаря этому файлы, созданные внутри контейнера (например, кэш Symfony или сгенерированные контроллеры), будут сразу доступны для редактирования и удаления в вашей IDE на хост-машине без использования `sudo`.
Но! Если что-то, или кто-то косякнул, то фиксится из хоста:
```bash
sudo chown -R $USER:33 app/ && sudo chmod -R 775 app/
```


### Вход в контейнер для разработки
Для запуска консоли Symfony, работы с Composer или генерации кода используйте стандартную команду входа:
```bash
docker compose exec php bash
```
*Внутри контейнера вы автоматически будете работать от имени пользователя `www-data`, чей UID равен вашему локальному UID (например, 1000). Конфликты прав полностью исключены.*


### Если нужно зайти под root (для установки пакетов через apt)
Если вам необходимо установить в сам контейнер какие-то системные утилиты, используйте флаг `--user root`:
```bash
docker compose exec --user root php bash
```
