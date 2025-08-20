ALTER TABLE `#__bookingmanager_supplier_markets` ADD `currency_symbol` VARCHAR(5) NULL DEFAULT NULL AFTER `currency`;
ALTER TABLE `#__bookingmanager_suppliers` ADD COLUMN `out_of_season_surcharge` DECIMAL(5,2) NOT NULL DEFAULT 10.00;
ALTER TABLE `#__bookingmanager_suppliers` ADD COLUMN `global_discount` DECIMAL(5,2) NOT NULL DEFAULT 0.00;
