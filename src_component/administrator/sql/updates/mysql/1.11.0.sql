ALTER TABLE `#__bookingmanager_properties` DROP COLUMN `region`;
ALTER TABLE `#__bookingmanager_properties` ADD `main_region_id` INT(11) NOT NULL DEFAULT 0 AFTER `max_guests`;
ALTER TABLE `#__bookingmanager_properties` ADD `sub_region_id` INT(11) NOT NULL DEFAULT 0 AFTER `main_region_id`;
