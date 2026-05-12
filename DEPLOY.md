# Инструкция: Развёртывание сайта OpenCart на локальном хостинге

## Что у вас есть (файлы с Google Drive)

| Файл | Описание |
|------|----------|
| `storage.zip` (~668 МБ) | Архив с файлами сайта: `public_html/` (код, темы, модули, изображения) + `storage/` (кэш, модификации, логи) |
| `cl244319_smtochk_*.sql` (~6.8 МБ) | Дамп базы данных — только данные (INSERT), без структуры таблиц |
| `opencart-3.0.4.1-rs (1).zip` (~17 МБ) | Чистая версия OpenCart 3.0.4.1 русская сборка (нужна для структуры таблиц) |
| `SmOcFilter.ocmod.zip` (~105 КБ) | Модуль OCFilter 4.7.5 |

---

## Требования

- **Docker Desktop** (Windows/Mac) или **Docker + Docker Compose** (Linux)
- **Git** (для клонирования репозитория)
- ~3 ГБ свободного места на диске
- Терминал (PowerShell / Terminal / bash)

### Установка Docker

- **Windows**: скачать с https://www.docker.com/products/docker-desktop/
- **Mac**: скачать с https://www.docker.com/products/docker-desktop/
- **Linux (Ubuntu)**:
  ```bash
  sudo apt update
  sudo apt install docker.io docker-compose -y
  sudo usermod -aG docker $USER
  # Перелогиньтесь после этого
  ```

---

## Пошаговая инструкция

### Шаг 1. Клонируйте репозиторий

```bash
git clone https://github.com/48Lipetsk/Fix-my-cite.git
cd Fix-my-cite
```

Репозиторий уже содержит:
- Все файлы сайта (`site/public_html/` и `site/storage/`)
- Готовые SQL-скрипты для базы данных (`sql/`)
- Docker-конфигурацию (`docker-compose.yml`, `docker/php/Dockerfile`)
- Исправленный `twig.php` для OCFilter (`fixes/twig.php`)

### Шаг 2. Запустите Docker-контейнеры

```bash
docker-compose up -d --build
```

Эта команда создаёт и запускает 3 контейнера:

| Контейнер | Назначение | Порт |
|-----------|-----------|------|
| `web` | PHP 8.1 + Apache (сайт) | `localhost:8080` |
| `db` | MariaDB 10.11 (база данных) | `localhost:3306` |
| `phpmyadmin` | phpMyAdmin (управление БД) | `localhost:8081` |

**Первый запуск** займёт 2–5 минут:
- Скачиваются Docker-образы (~500 МБ)
- Собирается PHP-контейнер с нужными расширениями
- Импортируются SQL-скрипты (структура + данные)

### Шаг 3. Дождитесь инициализации базы данных

Проверьте статус:

```bash
docker-compose logs db 2>&1 | tail -10
```

Когда увидите строку `mariadb-entrypoint: ... ready for connections` — база готова.

Если хотите убедиться, что все таблицы на месте:

```bash
docker exec fix-my-cite-db-1 mysql -uopencart -popencart opencart -e "SHOW TABLES" | wc -l
```

Должно быть **~189 таблиц**.

### Шаг 4. Исправьте права доступа

После первого запуска нужно выставить права на директории кэша и изображений:

```bash
docker exec fix-my-cite-web-1 chown -R www-data:www-data /var/www/html/image/cache
docker exec fix-my-cite-web-1 chmod -R 777 /var/www/html/image/cache
docker exec fix-my-cite-web-1 chown -R www-data:www-data /var/www/storage
docker exec fix-my-cite-web-1 chmod -R 777 /var/www/storage
```

> **Примечание**: Имя контейнера может отличаться. Проверьте через `docker ps` — ищите контейнер с портом 8080.

### Шаг 5. Очистите кэш шаблонов

```bash
docker exec fix-my-cite-web-1 rm -rf /var/www/storage/cache/template/*
```

### Шаг 6. Откройте сайт

| Что | URL | Логин | Пароль |
|-----|-----|-------|--------|
| **Магазин** (витрина) | http://localhost:8080 | — | — |
| **Админ-панель** | http://localhost:8080/admin | Gimka2281 | admin |
| **phpMyAdmin** | http://localhost:8081 | opencart | opencart |

> **Важно**: пароль админки в Docker-окружении сброшен на `admin`. Оригинальный пароль от вашего сервера здесь не подойдёт.

---

## Структура проекта

```
Fix-my-cite/
│
├── docker-compose.yml              # Конфигурация Docker (3 контейнера)
├── docker/
│   └── php/
│       └── Dockerfile              # Сборка PHP 8.1 + Apache + расширения
│
├── sql/                            # SQL-скрипты (выполняются автоматически при первом запуске)
│   ├── 01_opencart_base.sql        # Структура базовых таблиц OpenCart (CREATE TABLE)
│   ├── 02_missing_tables.sql       # Доп. таблицы для ProStore + OCFilter (CREATE TABLE + ALTER)
│   ├── 03_data_dump.sql            # Данные сайта (INSERT) — товары, категории, заказы и т.д.
│   ├── 03_ocfilter_setup.sql       # Регистрация модуля OCFilter в БД
│   └── 04_ocfilter_modification.sql # Регистрация OCMOD-модификации OCFilter
│
├── fixes/
│   └── twig.php                    # Исправленный twig.php (ChainLoader вместо ArrayLoader)
│
├── site/
│   ├── public_html/                # Файлы сайта OpenCart
│   │   ├── admin/                  # Админка
│   │   ├── catalog/                # Каталог (контроллеры, модели, шаблоны)
│   │   ├── system/                 # Системные файлы (вкл. исправленный twig.php)
│   │   ├── image/                  # Изображения товаров
│   │   └── config.php              # Конфигурация (настроен для Docker)
│   │
│   └── storage/                    # Хранилище OpenCart
│       ├── modification/           # OCMOD-модификации (патченные файлы)
│       ├── cache/                  # Кэш
│       ├── logs/                   # Логи
│       └── vendor/                 # Twig и другие библиотеки
│
└── README.md                       # Описание фикса OCFilter
```

---

## Как это работает (что делает Docker)

### docker-compose.yml

Определяет 3 сервиса:

1. **web** — PHP 8.1 с Apache
   - Монтирует `site/public_html/` в `/var/www/html` (файлы сайта)
   - Монтирует `site/storage/` в `/var/www/storage` (хранилище)
   - Слушает порт `8080`

2. **db** — MariaDB 10.11
   - Автоматически выполняет все `.sql` файлы из папки `sql/` при первом запуске
   - Данные сохраняются в Docker-томе `db_data` (не теряются при перезапуске)

3. **phpmyadmin** — веб-интерфейс для управления базой данных

### config.php

Файлы `config.php` (фронт + админка) настроены для Docker:
- `DB_HOSTNAME` = `db` (имя контейнера MariaDB)
- `DB_USERNAME` = `opencart`
- `DB_PASSWORD` = `opencart`
- `HTTP_SERVER` = `http://localhost:8080/`
- `DIR_STORAGE` = `/var/www/storage/`

### SQL-скрипты

Выполняются в алфавитном порядке при первом запуске MariaDB:

1. `01_opencart_base.sql` — 136 таблиц стандартного OpenCart 3.0.4.1-rs
2. `02_missing_tables.sql` — 52 доп. таблицы для ProStore + OCFilter + ALTER для недостающих колонок
3. `03_data_dump.sql` — данные вашего сайта (462 товара, 89 категорий, 16 заказов, 6 клиентов)
4. `03_ocfilter_setup.sql` — регистрация модуля OCFilter
5. `04_ocfilter_modification.sql` — регистрация OCMOD-модификации

---

## Частые проблемы и решения

### Проблема: Сайт не загружается, белая страница

**Решение**: Проверьте логи:
```bash
docker-compose logs web | tail -30
```

Часто причина — права доступа. Выполните шаг 4 (исправление прав).

### Проблема: Ошибка 500 на страницах категорий

**Решение**: Очистите кэш и проверьте логи PHP:
```bash
docker exec fix-my-cite-web-1 rm -rf /var/www/storage/cache/*
docker exec fix-my-cite-web-1 cat /var/www/storage/logs/php_errors.log | tail -20
```

### Проблема: Изображения не отображаются

**Решение**: Выставьте права на директорию изображений:
```bash
docker exec fix-my-cite-web-1 chown -R www-data:www-data /var/www/html/image
docker exec fix-my-cite-web-1 chmod -R 755 /var/www/html/image
```

### Проблема: «Неправильная токен-сессия» при входе в админку

**Решение**: Это нормально, просто введите логин/пароль ещё раз.

### Проблема: Порт 8080 уже занят

**Решение**: Измените порт в `docker-compose.yml`:
```yaml
ports:
  - "9090:80"  # Поменяйте 8080 на любой свободный
```
Затем обновите `HTTP_SERVER` в обоих `config.php` файлах.

### Проблема: База не импортируется / ошибки SQL

**Решение**: Удалите том с данными и перезапустите:
```bash
docker-compose down -v
docker-compose up -d --build
```
Флаг `-v` удаляет том с данными БД, и при следующем запуске SQL-скрипты выполнятся заново.

---

## Управление контейнерами

```bash
# Запуск
docker-compose up -d

# Остановка (данные сохраняются)
docker-compose down

# Остановка + удаление данных БД (полный сброс)
docker-compose down -v

# Перезапуск
docker-compose restart

# Просмотр логов
docker-compose logs -f          # Все контейнеры
docker-compose logs -f web      # Только веб-сервер
docker-compose logs -f db       # Только база данных

# Зайти в контейнер (для отладки)
docker exec -it fix-my-cite-web-1 bash    # В веб-сервер
docker exec -it fix-my-cite-db-1 bash     # В базу данных

# Проверить статус
docker-compose ps
```

---

## Как применить фикс OCFilter на продакшен-сервере

Если вы хотите применить этот фикс на своём рабочем сайте (не в Docker), вам нужно изменить **только 2 файла**:

### 1. Замените `twig.php`

Скопируйте файл `fixes/twig.php` из этого репозитория в два места на сервере:

```bash
# Основной файл
cp fixes/twig.php /путь/к/сайту/public_html/system/library/template/twig.php

# Модификационная копия (если есть)
cp fixes/twig.php /путь/к/сайту/storage/modification/system/library/template/twig.php
```

### 2. Зарегистрируйте OCFilter в startup.php

Откройте файл `storage/modification/catalog/controller/startup/startup.php` и после строки:

```php
$this->registry->set('cart', new Cart\Cart($this->registry));
```

добавьте:

```php
// OCFilter
$this->registry->set('ocfilter', new OCFilter($this->registry));
```

### 3. Очистите кэш

```bash
rm -rf /путь/к/сайту/storage/cache/template/*
rm -rf /путь/к/сайту/storage/cache/cache.*
```

### 4. Обновите OCMOD в админке

Зайдите: **Админка → Расширения → Модификаторы → синяя кнопка «Обновить»**

> **Внимание**: После обновления модификаторов в админке, файл `startup.php` в `storage/modification/` будет пересоздан и ваша правка потеряется. Нужно будет добавить строку OCFilter снова. Альтернативный вариант — добавить строку `$this->registry->set('ocfilter', ...)` в **оригинальный** `catalog/controller/startup/startup.php` (тогда она не будет затираться при обновлении модификаторов).

---

## Суть фикса OCFilter (техническая)

### Проблема

В OpenCart 3.0.4.1-rs файл `system/library/template/twig.php` использует `ArrayLoader`:

```php
$loader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));
```

`ArrayLoader` загружает шаблон только из переданной строки. Директива `{% include %}` не может найти другие файлы — она не знает, где они на диске.

OCFilter использует `{% include %}` для подгрузки компонентов:
```twig
{% include 'prostore/template/extension/module/ocfilter/filter_price.twig' %}
{% include 'prostore/template/extension/module/ocfilter/filter_list.twig' %}
```

Результат: ошибка 400 или пустой виджет.

### Решение

Заменяем `ArrayLoader` на `ChainLoader`, который пробует загрузчики по цепочке:

1. **`ArrayLoader`** — ищет шаблон в переданной строке (как раньше)
2. **`FilesystemLoader`** — ищет файлы на диске в `DIR_TEMPLATE` и `storage/modification/catalog/view/theme/`

```php
$arrayLoader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));
$filesystemLoader = new \Twig\Loader\FilesystemLoader($paths);
$loader = new \Twig\Loader\ChainLoader(array($arrayLoader, $filesystemLoader));
```

Это **безопасное** изменение — существующие шаблоны продолжают работать через `ArrayLoader`, а `{% include %}` теперь может найти файлы через `FilesystemLoader`.
