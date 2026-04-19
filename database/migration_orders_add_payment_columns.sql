-- Run this on your existing database if checkout fails with:
-- Unknown column 'payment_method' in 'field list'
-- (Your `orders` table was created before PayPal columns were added.)

ALTER TABLE `orders`
  ADD COLUMN `payment_method` varchar(50) DEFAULT 'PayPal' AFTER `status`,
  ADD COLUMN `paypal_order_id` varchar(100) DEFAULT NULL AFTER `payment_method`,
  ADD COLUMN `paypal_capture_id` varchar(100) DEFAULT NULL AFTER `paypal_order_id`;
