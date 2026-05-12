-- ==============================================================
-- OCFilter module registration for OpenCart 3.0.4.1-rs
-- This registers the ocfilter extension, module, and layout position
-- ==============================================================

-- Register OCFilter as an extension
INSERT INTO `oc_extension` (`type`, `code`) VALUES ('module', 'ocfilter')
  ON DUPLICATE KEY UPDATE `code` = 'ocfilter';

-- Register OCFilter module with default settings
INSERT INTO `oc_module` (`name`, `code`, `setting`) VALUES 
  ('OCFilter', 'ocfilter', '{"name":"OCFilter","status":"1","show_price":"1","show_counter":"1","search_button":"0","manual_price":"0"}');

-- Add OCFilter to category page layout (layout_id=3 = Category)
-- position column_left, sort_order 1
INSERT INTO `oc_layout_module` (`layout_id`, `code`, `position`, `sort_order`) 
  SELECT 3, CONCAT('ocfilter.', (SELECT module_id FROM oc_module WHERE code='ocfilter' LIMIT 1)), 'column_left', 1
  FROM dual
  WHERE NOT EXISTS (
    SELECT 1 FROM oc_layout_module WHERE code LIKE 'ocfilter%' AND layout_id = 3
  );

-- Enable OCFilter module status in settings
INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES 
  (0, 'module_ocfilter', 'module_ocfilter_status', '1', 0)
  ON DUPLICATE KEY UPDATE `value` = '1';
