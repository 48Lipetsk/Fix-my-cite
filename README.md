# Фикс OCFilter для OpenCart 3.0.4.1-rs + шаблон ProStore

## 🔍 Описание проблемы

В OpenCart 3.0.4.1-rs в файле `system/library/template/twig.php` используется **только `ArrayLoader`** для загрузки Twig-шаблонов. Это значит, что Twig умеет рендерить только один шаблон — тот, который передан напрямую как строка.

Модуль **OCFilter** (и другие модули) используют директиву `{% include %}` в своих шаблонах, например:

```twig
{% include 'prostore/template/extension/module/ocfilter/selected_filter.twig' %}
{% include 'prostore/template/extension/module/ocfilter/filter_price.twig' %}
{% include 'prostore/template/extension/module/ocfilter/filter_list.twig' %}
```

`ArrayLoader` **не умеет** находить файлы по путям — он работает только с массивом строк. Результат: **ошибка 400** или пустой виджет фильтра.

---

## ✅ Решение

Заменяем `ArrayLoader` на **`ChainLoader`**, который объединяет:

1. **`ArrayLoader`** — для текущего шаблона (передаётся как строка, как и раньше)
2. **`FilesystemLoader`** — для `{% include %}` директив (ищет файлы в папках тем)

### Файл для замены

📁 `system/library/template/twig.php`

### Что было (строка ~31):

```php
$loader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));
$twig = new \Twig\Environment($loader, $config);
```

### Что стало:

```php
// ArrayLoader — для текущего шаблона
$arrayLoader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));

// FilesystemLoader — для {% include %} директив
$paths = array();

if (defined('DIR_TEMPLATE')) {
    $paths[] = DIR_TEMPLATE;
}

if (defined('DIR_MODIFICATION') && is_dir(DIR_MODIFICATION . 'catalog/view/theme/')) {
    $paths[] = DIR_MODIFICATION . 'catalog/view/theme/';
}

$filesystemLoader = new \Twig\Loader\FilesystemLoader($paths);

// ChainLoader: сначала ArrayLoader, потом FilesystemLoader
$loader = new \Twig\Loader\ChainLoader(array($arrayLoader, $filesystemLoader));

$twig = new \Twig\Environment($loader, $config);
```

---

## 📋 Пошаговая инструкция для применения на вашем сервере

### Вариант 1: Быстрая замена одного файла (рекомендуется)

1. **Сделайте резервную копию** оригинального файла:
   ```bash
   cp /home/c/cl244319/Smart-Tochka/public_html/system/library/template/twig.php \
      /home/c/cl244319/Smart-Tochka/public_html/system/library/template/twig.php.bak
   ```

2. **Скопируйте исправленный файл** из папки `fixes/`:
   ```bash
   cp fixes/twig.php \
      /home/c/cl244319/Smart-Tochka/public_html/system/library/template/twig.php
   ```

3. **Очистите кэш шаблонов** OpenCart:
   ```bash
   rm -rf /home/c/cl244319/Smart-Tochka/storage/cache/template/*
   ```

4. **Исправьте также модификационную копию** (если есть):
   ```bash
   cp fixes/twig.php \
      /home/c/cl244319/Smart-Tochka/storage/modification/system/library/template/twig.php
   ```

5. **Зарегистрируйте OCFilter библиотеку** в startup.php:
   
   Откройте файл `storage/modification/catalog/controller/startup/startup.php` и после строки:
   ```php
   $this->registry->set('cart', new Cart\Cart($this->registry));
   ```
   добавьте:
   ```php
   // OCFilter
   $this->registry->set('ocfilter', new OCFilter($this->registry));
   ```

6. **Очистите кэш шаблонов** OpenCart:
   ```bash
   rm -rf /home/c/cl244319/Smart-Tochka/storage/cache/template/*
   ```

7. **Обновите OCMOD**: Зайдите в Админку → Расширения → Модификаторы → нажмите синюю кнопку «Обновить».

8. **Проверьте сайт** — откройте любую категорию с товарами. Фильтр OCFilter должен отображаться.

### Вариант 2: Ручное редактирование

1. Откройте файл `system/library/template/twig.php` в текстовом редакторе.

2. Найдите строку (примерно строка 31):
   ```php
   $loader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));
   ```

3. Замените блок от этой строки до `$twig = new \Twig\Environment($loader, $config);` на код из раздела «Что стало» выше.

4. Сохраните файл и очистите кэш.

---

## 🐳 Локальный запуск через Docker (для тестирования)

### Требования

- Docker и Docker Compose
- ~2 ГБ свободного места

### Запуск

```bash
# 1. Клонируйте репозиторий
git clone https://github.com/48Lipetsk/Fix-my-cite.git
cd Fix-my-cite

# 2. Запустите контейнеры
docker-compose up -d --build

# 3. Подождите ~30 секунд, пока БД импортирует данные
# Проверить статус:
docker-compose logs db | tail -20
```

### Доступ

| Сервис       | URL                         | Логин/Пароль            |
|-------------|-----------------------------|-----------------------|
| Магазин     | http://localhost:8080       | —                     |
| Админка     | http://localhost:8080/admin | admin / admin (стандартный) |
| phpMyAdmin  | http://localhost:8081       | opencart / opencart   |

### Остановка

```bash
docker-compose down

# Для полного удаления данных БД:
docker-compose down -v
```

---

## 📁 Структура репозитория

```
Fix-my-cite/
├── docker-compose.yml          # Docker Compose конфигурация
├── docker/
│   └── php/
│       └── Dockerfile          # PHP 8.1 + Apache + модули
├── sql/
│   ├── 01_opencart_base.sql    # Базовые таблицы OpenCart 3.0.4.1-rs
│   ├── 02_missing_tables.sql   # Недостающие таблицы (ProStore + OCFilter)
│   ├── 03_ocfilter_setup.sql   # Регистрация модуля OCFilter в БД
│   ├── 04_ocfilter_modification.sql # Регистрация OCMOD модификации
│   └── 05_data_dump.sql        # Дамп данных сайта
├── fixes/
│   └── twig.php                # Исправленный twig.php (готов к копированию)
├── site/
│   ├── public_html/            # Файлы сайта (OpenCart + ProStore + OCFilter)
│   │   ├── admin/              # Админка
│   │   ├── catalog/            # Каталог (шаблоны, контроллеры, модели)
│   │   ├── system/             # Системные файлы (включая исправленный twig.php)
│   │   ├── image/              # Изображения
│   │   └── config.php          # Конфигурация (для Docker)
│   └── storage/                # Хранилище (кэш, логи, сессии)
│       └── modification/       # OCMOD-модификации
└── README.md                   # Эта инструкция
```

---

## ⚠️ Важные замечания

1. **config.php** в репозитории настроен для Docker. На продакшене используйте свой оригинальный config.php — менять нужно **только** `twig.php`.

2. **Кэш шаблонов** (`storage/cache/template/`) нужно очищать после применения фикса. Иначе Twig может использовать старый скомпилированный шаблон.

3. **OCMOD-модификации**: После установки OCFilter через админку (Расширения → Установщик расширений), не забудьте зайти в Расширения → Модификаторы и нажать кнопку «Обновить» (синяя кнопка).

4. Этот фикс **безопасен** — он не ломает работу существующих шаблонов, только добавляет возможность использовать `{% include %}`.
