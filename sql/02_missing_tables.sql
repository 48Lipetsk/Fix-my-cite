-- ==============================================================
-- Missing tables and columns for prostore theme + ocfilter module
-- These tables/columns are NOT part of the base OpenCart 3.0.4.1-rs
-- ==============================================================

-- ---- ALTER existing base tables to add missing columns ----

ALTER TABLE `oc_product` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `oc_product_option_value` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `oc_product_option_value` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `oc_product_to_category` ADD COLUMN `main_category` TINYINT(1) NOT NULL DEFAULT '0';

-- ---- OCFilter tables (10) ----

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
  `language_id` TINYINT(2) NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `postfix` VARCHAR(32) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`option_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_to_category` (
  `option_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`,`option_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_to_store` (
  `option_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`store_id`,`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value` (
  `value_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `option_id` INT(11) NOT NULL DEFAULT '0',
  `keyword` VARCHAR(255) NOT NULL DEFAULT '',
  `color` VARCHAR(6) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`value_id`),
  KEY `option_id` (`option_id`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value_description` (
  `value_id` BIGINT(20) NOT NULL,
  `option_id` INT(11) NOT NULL,
  `language_id` TINYINT(2) NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`value_id`,`language_id`),
  KEY `option_id` (`option_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value_to_product` (
  `ocfilter_option_value_to_product_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `option_id` INT(11) NOT NULL,
  `value_id` BIGINT(20) NOT NULL,
  `slide_value_min` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `slide_value_max` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ocfilter_option_value_to_product_id`),
  UNIQUE INDEX `option_id_value_id_product_id` (`option_id`, `value_id`, `product_id`),
  INDEX `slide_value_min_slide_value_max` (`slide_value_min`, `slide_value_max`),
  INDEX `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_option_value_to_product_description` (
  `product_id` INT(11) NOT NULL,
  `value_id` BIGINT(20) NOT NULL,
  `option_id` INT(11) NOT NULL,
  `language_id` TINYINT(2) NOT NULL,
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`product_id`,`value_id`,`option_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_page` (
  `ocfilter_page_id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL DEFAULT '0',
  `keyword` VARCHAR(255) NOT NULL,
  `params` VARCHAR(255) NOT NULL,
  `over` SET('domain','category') NOT NULL DEFAULT 'category',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ocfilter_page_id`),
  INDEX `keyword` (`keyword`),
  INDEX `category_id_params` (`category_id`, `params`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_ocfilter_page_description` (
  `ocfilter_page_id` INT(11) NOT NULL DEFAULT '0',
  `language_id` INT(11) NOT NULL DEFAULT '0',
  `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_h1` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_description` TEXT NOT NULL,
  `meta_keyword` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  PRIMARY KEY (`ocfilter_page_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ---- ProStore theme tables ----

CREATE TABLE IF NOT EXISTS `oc_prostore_blog` (
  `blog_id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `image_preview` VARCHAR(255) DEFAULT NULL,
  `bottom` TINYINT(1) NOT NULL DEFAULT '0',
  `sort_order` INT(3) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `viewed` INT(11) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_banner` (
  `banner_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `image_thumb_width` INT(11) NOT NULL DEFAULT '0',
  `image_thumb_height` INT(11) NOT NULL DEFAULT '0',
  `image_popup_width` INT(11) NOT NULL DEFAULT '0',
  `image_popup_height` INT(11) NOT NULL DEFAULT '0',
  `template` VARCHAR(32) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_banner_image` (
  `banner_image_id` INT(11) NOT NULL AUTO_INCREMENT,
  `banner_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `sort_order` INT(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`banner_image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_comment` (
  `comment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `blog_id` INT(11) NOT NULL DEFAULT '0',
  `customer_id` INT(11) NOT NULL DEFAULT '0',
  `author` VARCHAR(64) NOT NULL DEFAULT '',
  `text` TEXT NOT NULL,
  `rating` INT(1) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_description` (
  `blog_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_h1` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_keyword` VARCHAR(255) NOT NULL DEFAULT '',
  `tag` TEXT NOT NULL,
  PRIMARY KEY (`blog_id`,`language_id`),
  KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related_banners` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related_cat` (
  `blog_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_related_prod` (
  `blog_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_tag` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `blog_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `tag` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_to_category` (
  `blog_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `main_category` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_to_layout` (
  `blog_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `layout_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_blog_to_store` (
  `blog_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_callback` (
  `callback_id` INT(11) NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`callback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_callcheaper` (
  `call_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `telephone` VARCHAR(32) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `link` VARCHAR(255) NOT NULL DEFAULT '',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  `status_id` TINYINT(1) NOT NULL DEFAULT '0',
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `comment` TEXT NOT NULL,
  PRIMARY KEY (`call_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_field` (
  `custom_field_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL,
  `type` VARCHAR(32) NOT NULL,
  `sort_order` INT(3) NOT NULL,
  PRIMARY KEY (`custom_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_field_customer_group` (
  `custom_field_id` INT(11) NOT NULL,
  `customer_group_id` INT(11) NOT NULL,
  `required` TINYINT(1) NOT NULL,
  `is_show` TINYINT(1) NOT NULL DEFAULT '0',
  `store_id` INT(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_field_description` (
  `custom_field_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `description` TEXT NOT NULL,
  `store_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`custom_field_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs` (
  `cust_tab_id` INT(11) NOT NULL AUTO_INCREMENT,
  `view` VARCHAR(32) NOT NULL DEFAULT 'tab',
  `mode` VARCHAR(32) NOT NULL DEFAULT 'categories',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`cust_tab_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_description` (
  `tab_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL,
  `heading` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  PRIMARY KEY (`tab_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_instanses` (
  `cust_tab_id` INT(11) NOT NULL,
  `instanses` INT(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_instanses_brands` (
  `tab_instanse_brand_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tab_id` INT(11) NOT NULL,
  `manufacturer_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tab_instanse_brand_id`),
  KEY `tab_id` (`tab_id`),
  KEY `manufacturer_id` (`manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_custom_tabs_to_store` (
  `cust_tab_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`cust_tab_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_faq` (
  `faq_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL DEFAULT '0',
  `customer_id` INT(11) NOT NULL DEFAULT '0',
  `author` VARCHAR(64) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `text` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `text_admin_answer` TEXT NOT NULL,
  `answer_date_added` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`faq_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_key` (
  `value` TEXT NOT NULL,
  `key` VARCHAR(255) NOT NULL DEFAULT '',
  `license_key` VARCHAR(255) NOT NULL DEFAULT '',
  `date_added` DATE NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_news_related` (
  `news_id` INT(11) NOT NULL,
  `related_id` INT(11) NOT NULL,
  PRIMARY KEY (`news_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_product_to_set` (
  `set_product_id` INT(11) NOT NULL AUTO_INCREMENT,
  `set_id` INT(11) NOT NULL,
  `row_id` INT(11) NOT NULL DEFAULT '0',
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT '1',
  `sort_order` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_review_shop` (
  `review_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL DEFAULT '0',
  `author` VARCHAR(64) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `text` TEXT NOT NULL,
  `r1` INT(1) NOT NULL DEFAULT '0',
  `r2` INT(1) NOT NULL DEFAULT '0',
  `r3` INT(1) NOT NULL DEFAULT '0',
  `r4` INT(1) NOT NULL DEFAULT '0',
  `r5` INT(1) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  `text_admin_answer` TEXT NOT NULL,
  `answer_date_added` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set` (
  `set_id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(3) NOT NULL DEFAULT '0',
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
  `meta_title` VARCHAR(255) NOT NULL,
  `meta_description` VARCHAR(255) NOT NULL,
  `meta_keyword` VARCHAR(255) NOT NULL,
  `meta_h1` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`set_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set_to_layout` (
  `set_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `layout_id` INT(11) NOT NULL,
  PRIMARY KEY (`set_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_set_to_store` (
  `set_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_subscribe` (
  `subscribe_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`subscribe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_subscribe_auth_description` (
  `subscribe_id` INT(11) NOT NULL AUTO_INCREMENT,
  `language_id` INT(11) NOT NULL,
  `heading` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  PRIMARY KEY (`subscribe_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostore_subscribe_email_description` (
  `subscribe_id` INT(11) NOT NULL AUTO_INCREMENT,
  `language_id` INT(11) NOT NULL,
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  PRIMARY KEY (`subscribe_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `parent_id` INT(11) NOT NULL DEFAULT '0',
  `top` TINYINT(1) NOT NULL DEFAULT '0',
  `column` INT(3) NOT NULL DEFAULT '0',
  `sort_order` INT(3) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
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
  `meta_h1` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_filter` (
  `category_id` INT(11) NOT NULL,
  `filter_id` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`,`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_path` (
  `category_id` INT(11) NOT NULL,
  `path_id` INT(11) NOT NULL,
  `level` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`path_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_to_layout` (
  `category_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `layout_id` INT(11) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_prostorecat_blog_to_store` (
  `category_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ---- Extra table for extended reviews ----

CREATE TABLE IF NOT EXISTS `oc_review_extls` (
  `review_id` INT(11) NOT NULL,
  `likes` INT(11) NOT NULL DEFAULT '0',
  `dislikes` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
