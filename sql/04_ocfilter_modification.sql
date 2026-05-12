-- ==============================================================
-- Register OCFilter OCMOD modification in oc_modification table
-- The actual XML is loaded from SmOcFilter.ocmod.zip install.xml
-- This entry enables the modification in the admin panel
-- ==============================================================

INSERT INTO `oc_modification` (`name`, `code`, `author`, `version`, `link`, `xml`, `status`, `date_added`)
VALUES (
  'OCFilter Modification',
  'ocfilter-product-filter',
  'prowebber.ru',
  '4.7.5',
  'https://prowebber.ru/',
  '',
  1,
  NOW()
) ON DUPLICATE KEY UPDATE `status` = 1;
