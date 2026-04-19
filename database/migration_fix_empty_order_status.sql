-- Fix orders that show blank / invalid status in the app (appears as "Unknown").
-- Run once on your database.

UPDATE `orders`
SET `status` = 'Paid'
WHERE (`status` IS NULL OR `status` = '')
  AND (`paypal_order_id` IS NOT NULL AND `paypal_order_id` <> ''
       OR `paypal_capture_id` IS NOT NULL AND `paypal_capture_id` <> '');

UPDATE `orders`
SET `status` = 'Pending'
WHERE (`status` IS NULL OR `status` = '')
  AND (`paypal_order_id` IS NULL OR `paypal_order_id` = '')
  AND (`paypal_capture_id` IS NULL OR `paypal_capture_id` = '');
