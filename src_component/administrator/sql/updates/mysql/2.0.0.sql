CREATE TABLE `#__bookingmanager_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `#__bookingmanager_properties` ADD `main_region_id` INT(11) NULL;
ALTER TABLE `#__bookingmanager_properties` ADD `sub_region_id` INT(11) NULL;
