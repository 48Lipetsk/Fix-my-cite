# Развёртывание сайта «Смарт Точка» на XAMPP

Пошаговая инструкция по установке сайта на OpenCart 3.0.4.1-rs с шаблоном ProStore на локальном сервере XAMPP.

---

## Что вам понадобится

Файлы с Google Drive:
- `opencart-3.0.4.1-rs (1).zip` — чистый OpenCart
- `storage.zip` — файлы вашего сайта (public_html + storage)
- `cl244319_smtochk_*.sql` — дамп базы данных
- `SmOcFilter.ocmod.zip` — модуль OCFilter (опционально)

Дополнительно из этого репозитория:
- `sql/02_missing_tables.sql` — SQL-скрипт с недостающими таблицами и колонками

---

## Шаг 1. Установите XAMPP

1. Скачайте XAMPP с https://www.apachefriends.org/
   - Выберите версию с **PHP 8.1** (OpenCart 3.0.4.1-rs требует PHP 7.3–8.1)
2. Установите XAMPP (по умолчанию в `C:\xampp`)
3. Запустите **Apache** и **MySQL** через XAMPP Control Panel

---

## Шаг 2. Установите чистый OpenCart

1. Распакуйте `opencart-3.0.4.1-rs (1).zip`
2. Скопируйте содержимое папки `upload-3041-rs1/` в `C:\xampp\htdocs\smtochka\`
   
   > В итоге у вас должно быть: `C:\xampp\htdocs\smtochka\index.php`, `C:\xampp\htdocs\smtochka\admin\`, и т.д.

3. Создайте базу данных:
   - Откройте phpMyAdmin: http://localhost/phpmyadmin
   - Нажмите **«Создать базу данных»** (или «New»)
   - Имя: `smtochka`
   - Сравнение: `utf8_general_ci`
   - Нажмите **«Создать»**

4. Установите OpenCart через браузер:
   - Откройте http://localhost/smtochka/
   - Пройдите установку:
     - **БД хост**: `localhost`
     - **БД пользователь**: `root`
     - **БД пароль**: _(пустой, если не меняли)_
     - **БД имя**: `smtochka`
     - **Префикс таблиц**: `oc_`
     - Задайте логин/пароль администратора (любые, они будут перезаписаны дампом)
   - Дождитесь окончания установки

5. **Удалите папку install**:
   - Удалите `C:\xampp\htdocs\smtochka\install\`

---

## Шаг 3. Добавьте недостающие таблицы и колонки

> **Это ключевой шаг!** Ваш дамп содержит данные для таблиц ProStore и OCFilter, которых нет в стандартном OpenCart. Также дамп ожидает кастомные колонки (`link`, `is_default`, `main_category`) в стандартных таблицах. Без этого шага импорт дампа упадёт с ошибками типа «Unknown column 'link'».

1. Откройте phpMyAdmin: http://localhost/phpmyadmin
2. Выберите базу `smtochka`
3. Перейдите на вкладку **«SQL»**
4. Скопируйте и выполните **весь** SQL-скрипт ниже:

```sql
-- ==============================================================
-- Недостающие колонки в стандартных таблицах OpenCart
-- ==============================================================

-- Колонка link в oc_product (ваша кастомная колонка для опций)
ALTER TABLE `oc_product` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL;

-- Колонки is_default и link в oc_product_option_value
ALTER TABLE `oc_product_option_value` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `oc_product_option_value` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL;

-- Колонка main_category в oc_product_to_category
ALTER TABLE `oc_product_to_category` ADD COLUMN `main_category` TINYINT(1) NOT NULL DEFAULT '0';

-- ==============================================================
-- Таблицы OCFilter (10 таблиц)
-- ==============================================================

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option` (
  `option_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(16) NOT NULL DEFAULT 'checkbox',
  `keyword` VARCHAR(255) NOT NULL DEFAULT '',
  `selectbox` TINYINT(1) NOT NULL DEFAULT '0',
  `grouping` TINYINT(2) NOT NULL DEFAULT '0',
  `color` TINYINT(1) NOT NULL DEFAULT '0',
  `image` TINYINT(1) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`option_id`),
  KEY `keyword` (`keyword`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_description` (
  `option_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `tag` TEXT NOT NULL,
  PRIMARY KEY (`option_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_to_category` (
  `option_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  PRIMARY KEY (`option_id`, `category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_to_store` (
  `option_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`option_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value` (
  `value_id` INT(11) NOT NULL AUTO_INCREMENT,
  `option_id` INT(11) NOT NULL,
  `keyword` VARCHAR(255) NOT NULL DEFAULT '',
  `color` VARCHAR(16) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`value_id`),
  KEY `option_id` (`option_id`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value_description` (
  `value_id` INT(11) NOT NULL,
  `option_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`value_id`, `option_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value_to_product` (
  `product_id` INT(11) NOT NULL,
  `option_id` INT(11) NOT NULL,
  `value_id` INT(11) NOT NULL,
  `slide_value_min` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `slide_value_max` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`product_id`, `option_id`, `value_id`),
  KEY `option_id` (`option_id`),
  KEY `value_id` (`value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value_to_product_description` (
  `product_id` INT(11) NOT NULL,
  `option_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `slide_value_min` VARCHAR(128) NOT NULL DEFAULT '',
  `slide_value_max` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`product_id`, `option_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_page` (
  `page_id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL DEFAULT '0',
  `keyword` VARCHAR(255) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`page_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_page_description` (
  `page_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `h1` VARCHAR(255) NOT NULL DEFAULT '',
  `h2` VARCHAR(255) NOT NULL DEFAULT '',
  `h3` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `description_bottom` TEXT NOT NULL,
  `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_keyword` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`page_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ==============================================================
-- Таблицы ProStore (41 таблица)
-- ==============================================================

CREATE TABLE IF NOT EXISTS `oc_prostore_blog` (
  `blog_id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `image_wide` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  `viewed` INT(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_banner` (
  `prostore_blog_banner_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `status` TINYINT(1) NOT NULL,
  PRIMARY KEY (`prostore_blog_banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_banner_image` (
  `prostore_blog_banner_image_id` INT(11) NOT NULL AUTO_INCREMENT,
  `prostore_blog_banner_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `title` VARCHAR(64) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `sort_order` INT(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`prostore_blog_banner_image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_comment` (
  `comment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `blog_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL DEFAULT '0',
  `author` VARCHAR(64) NOT NULL,
  `text` TEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_description` (
  `blog_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `tag` TEXT NOT NULL,
  `meta_title` VARCHAR(255) NOT NULL,
  `meta_description` VARCHAR(255) NOT NULL,
  `meta_keyword` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`blog_id`, `language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related_banners` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related_cat` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related_prod` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_tag` (
  `blog_id` INT(11) NOT NULL,
  `tag` VARCHAR(64) NOT NULL,
  KEY `blog_id` (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_to_category` (
  `blog_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_to_layout` (
  `blog_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `layout_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_to_store` (
  `blog_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_callback` (
  `callback_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `telephone` VARCHAR(32) NOT NULL,
  `product_id` INT(11) NOT NULL DEFAULT '0',
  `status_id` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`callback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_callcheaper` (
  `callcheaper_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `telephone` VARCHAR(32) NOT NULL,
  `link_competitor` VARCHAR(255) NOT NULL DEFAULT '',
  `product_id` INT(11) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`callcheaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_field` (
  `custom_field_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(32) NOT NULL,
  `value` TEXT NOT NULL,
  `validation` VARCHAR(255) NOT NULL,
  `location` VARCHAR(10) NOT NULL,
  `status` TINYINT(1) NOT NULL,
  `sort_order` INT(3) NOT NULL,
  PRIMARY KEY (`custom_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_field_customer_group` (
  `custom_field_id` INT(11) NOT NULL,
  `customer_group_id` INT(11) NOT NULL,
  `required` TINYINT(1) NOT NULL,
  PRIMARY KEY (`custom_field_id`, `customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_field_description` (
  `custom_field_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`custom_field_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs` (
  `tab_id` INT(11) NOT NULL AUTO_INCREMENT,
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tab_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_description` (
  `tab_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`tab_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_instanses` (
  `instanse_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tab_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL DEFAULT '0',
  `category_id` INT(11) NOT NULL DEFAULT '0',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`instanse_id`),
  KEY `tab_id` (`tab_id`),
  KEY `product_id` (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_instanses_brands` (
  `instanse_id` INT(11) NOT NULL,
  `manufacturer_id` INT(11) NOT NULL,
  PRIMARY KEY (`instanse_id`, `manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_to_store` (
  `tab_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`tab_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_faq` (
  `faq_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `author` VARCHAR(64) NOT NULL,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`faq_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_key` (
  `key_id` INT(11) NOT NULL AUTO_INCREMENT,
  `key_value` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`key_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_news_related` (
  `news_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`news_id`, `related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_product_to_set` (
  `product_id` INT(11) NOT NULL,
  `set_id` INT(11) NOT NULL,
  PRIMARY KEY (`product_id`, `set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_review_shop` (
  `review_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL DEFAULT '0',
  `author` VARCHAR(64) NOT NULL,
  `text` TEXT NOT NULL,
  `rating` INT(1) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set` (
  `set_id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set_description` (
  `set_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`set_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set_to_layout` (
  `set_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `layout_id` INT(11) NOT NULL,
  PRIMARY KEY (`set_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set_to_store` (
  `set_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`set_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_subscribe` (
  `subscribe_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(96) NOT NULL,
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`subscribe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_subscribe_auth_description` (
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  PRIMARY KEY (`store_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_subscribe_email_description` (
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `language_id` INT(11) NOT NULL,
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  PRIMARY KEY (`store_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `parent_id` INT(11) NOT NULL DEFAULT '0',
  `top` TINYINT(1) NOT NULL,
  `sort_order` INT(3) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_description` (
  `category_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `meta_title` VARCHAR(255) NOT NULL,
  `meta_description` VARCHAR(255) NOT NULL,
  `meta_keyword` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`category_id`, `language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_filter` (
  `category_id` INT(11) NOT NULL,
  `filter_id` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`, `filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_path` (
  `category_id` INT(11) NOT NULL,
  `path_id` INT(11) NOT NULL,
  `level` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`, `path_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_to_layout` (
  `category_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `layout_id` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_to_store` (
  `category_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ==============================================================
-- Таблица расширенных отзывов (ProStore)
-- ==============================================================

CREATE TABLE IF NOT EXISTS `oc_review_extls` (
  `review_id` INT(11) NOT NULL,
  `likes` INT(11) NOT NULL DEFAULT '0',
  `dislikes` INT(11) NOT NULL DEFAULT '0',
  `text_plus` TEXT NOT NULL,
  `text_minus` TEXT NOT NULL,
  `count_bad` INT(11) NOT NULL DEFAULT '0',
  `count_good` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

5. Нажмите **«Вперёд»** (или **«Go»**). Все команды должны выполниться без ошибок.

> **Что делает этот скрипт:**
> - Добавляет колонку `link` в таблицу `oc_product` (ваша кастомная колонка)
> - Добавляет колонки `is_default` и `link` в `oc_product_option_value`
> - Добавляет колонку `main_category` в `oc_product_to_category`
> - Создаёт 10 таблиц OCFilter (`oc_ocfilter_*`)
> - Создаёт 41 таблицу ProStore (`oc_prostore_*`, `oc_prostorecat_*`)
> - Создаёт таблицу `oc_review_extls` (расширенные отзывы)

---

## Шаг 4. Импортируйте дамп базы данных

1. В phpMyAdmin выберите базу `smtochka`
2. Перейдите на вкладку **«Импорт»**
3. Нажмите **«Выберите файл»** и выберите `cl244319_smtochk_*.sql`
4. Формат: **SQL**
5. Нажмите **«Вперёд»** (или **«Go»**)

Импорт должен пройти **без ошибок**, потому что все недостающие таблицы и колонки уже созданы на шаге 3.

> **Если phpMyAdmin ругается на размер файла** (дамп ~6.8 МБ):
> Откройте `C:\xampp\php\php.ini` и увеличьте:
> ```ini
> upload_max_filesize = 64M
> post_max_size = 64M
> ```
> Перезапустите Apache в XAMPP Control Panel.

---

## Шаг 5. Замените файлы сайта

1. Распакуйте `storage.zip` — он содержит две папки:
   - `public_html/` — файлы сайта (темы, модули, изображения)
   - `storage/` — хранилище OpenCart (кэш, модификации, вендоры)

2. **Скопируйте файлы сайта** (с заменой):
   - Содержимое `public_html/` → в `C:\xampp\htdocs\smtochka\`
   - Скопируйте с заменой существующих файлов
   
   > **Важно**: НЕ заменяйте `config.php` и `admin/config.php` — они были созданы установщиком и содержат правильные пути для вашего XAMPP.

3. **Скопируйте storage**:
   - Содержимое `storage/` → в `C:\xampp\htdocs\smtochka\system\storage\`
   
   > Или куда указывает `DIR_STORAGE` в вашем `config.php`. По умолчанию после установки OC 3.0.4.1-rs хранилище находится в `system/storage/`.

---

## Шаг 6. Настройте config.php

Откройте `C:\xampp\htdocs\smtochka\config.php` и убедитесь, что пути правильные:

```php
<?php
// HTTP
define('HTTP_SERVER', 'http://localhost/smtochka/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost/smtochka/');

// DIR
define('DIR_APPLICATION', 'C:/xampp/htdocs/smtochka/catalog/');
define('DIR_SYSTEM', 'C:/xampp/htdocs/smtochka/system/');
define('DIR_IMAGE', 'C:/xampp/htdocs/smtochka/image/');
define('DIR_STORAGE', 'C:/xampp/htdocs/smtochka/system/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'smtochka');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
```

Аналогично проверьте `C:\xampp\htdocs\smtochka\admin\config.php`:

```php
<?php
// HTTP
define('HTTP_SERVER', 'http://localhost/smtochka/admin/');
define('HTTP_CATALOG', 'http://localhost/smtochka/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost/smtochka/admin/');
define('HTTPS_CATALOG', 'http://localhost/smtochka/');

// DIR
define('DIR_APPLICATION', 'C:/xampp/htdocs/smtochka/admin/');
define('DIR_SYSTEM', 'C:/xampp/htdocs/smtochka/system/');
define('DIR_IMAGE', 'C:/xampp/htdocs/smtochka/image/');
define('DIR_STORAGE', 'C:/xampp/htdocs/smtochka/system/storage/');
define('DIR_CATALOG', 'C:/xampp/htdocs/smtochka/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'smtochka');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
```

---

## Шаг 7. Очистите кэш

Удалите содержимое папок кэша:
- `C:\xampp\htdocs\smtochka\system\storage\cache\` — удалите все файлы внутри
- `C:\xampp\htdocs\smtochka\system\storage\cache\template\` — удалите все файлы внутри

---

## Шаг 8. Откройте сайт

- **Магазин**: http://localhost/smtochka/
- **Админка**: http://localhost/smtochka/admin/

> Логин/пароль админки — те, которые были в вашем дампе (Gimka2281 / ваш оригинальный пароль).

---

## Возможные ошибки и решения

### Ошибка: «Unknown column 'link' in 'field list'» при импорте дампа

**Причина**: Вы не выполнили шаг 3 (добавление недостающих колонок) перед импортом дампа.

**Решение**: 
1. Удалите базу `smtochka` в phpMyAdmin
2. Создайте её заново
3. Заново установите OpenCart (шаг 2)
4. Выполните SQL-скрипт из шага 3
5. Только после этого импортируйте дамп (шаг 4)

### Ошибка: «Table 'oc_prostore_*' doesn't exist»

**Причина**: Те же — не выполнен шаг 3.

**Решение**: То же — пересоздайте БД и выполните шаги 2→3→4 по порядку.

### Ошибка 400 на страницах категорий (OCFilter не работает)

**Причина**: В OpenCart 3.0.4.1-rs в файле `twig.php` стоит `ArrayLoader`, который не поддерживает `{% include %}`.

**Решение**: Замените файл `system/library/template/twig.php` на исправленный из папки `fixes/` этого репозитория. Также замените копию в `storage/modification/system/library/template/twig.php`. Подробно — см. README.md.

### Белая страница / ошибка 500

**Решение**: Включите отображение ошибок PHP. Откройте `C:\xampp\php\php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
```
Перезапустите Apache и посмотрите, что за ошибка.

### phpMyAdmin не даёт загрузить дамп (файл слишком большой)

**Решение**: В `C:\xampp\php\php.ini`:
```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
```
Перезапустите Apache.

Альтернативный способ — импорт через командную строку:
```cmd
C:\xampp\mysql\bin\mysql.exe -u root smtochka < путь\к\дампу.sql
```

---

## Порядок действий (краткая шпаргалка)

```
1. Установить XAMPP (PHP 8.1)
2. Создать БД "smtochka" в phpMyAdmin
3. Установить чистый OpenCart 3.0.4.1-rs → http://localhost/smtochka/
4. Удалить папку install/
5. Выполнить SQL-скрипт с недостающими таблицами (шаг 3) ← ВАЖНО!
6. Импортировать дамп БД через phpMyAdmin
7. Распаковать storage.zip → скопировать файлы (без замены config.php!)
8. Проверить/исправить config.php (пути и БД)
9. Очистить кэш
10. Открыть сайт
```
