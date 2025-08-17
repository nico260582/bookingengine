-- This table will store the different price markets a supplier wants to use.
CREATE TABLE IF NOT EXISTS `#__bookingmanager_supplier_markets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `market_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- This modifies the existing rates table to store multiple market prices in a JSON format.
-- It changes the 'base_rate' column to 'rates' and allows it to store text.
ALTER TABLE `#__bookingmanager_rates` CHANGE `base_rate` `rates` TEXT NULL DEFAULT NULL;
