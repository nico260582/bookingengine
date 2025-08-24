CREATE TABLE IF NOT EXISTS `#__bookingmanager_main_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_sub_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `main_region_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_main_region_id` (`main_region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
