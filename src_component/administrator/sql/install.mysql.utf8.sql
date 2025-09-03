CREATE TABLE IF NOT EXISTS `#__bookingmanager_complexes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_complex_property_map` (
  `property_id` int NOT NULL,
  `complex_id` int NOT NULL,
  `priority` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`property_id`,`complex_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_main_regions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_properties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `max_guests` int NOT NULL DEFAULT '2',
  `number_of_units` int NOT NULL DEFAULT '1',
  `allow_extra_mattress` tinyint(1) NOT NULL DEFAULT '0',
  `main_region_id` int NOT NULL DEFAULT '0',
  `sub_region_id` int NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_property_map` (
  `property_id` int NOT NULL,
  `supplier_id` int NOT NULL,
  PRIMARY KEY (`property_id`),
  KEY `idx_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_rates` (
  `property_id` int NOT NULL,
  `season_name` varchar(255) NOT NULL,
  `rates` text,
  `active_markets` text,
  `base_guest_number` int DEFAULT NULL,
  `override_admin_commission` tinyint(1) NOT NULL DEFAULT '0',
  `admin_commission` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`property_id`,`season_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_sub_regions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `main_region_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_main_region_id` (`main_region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `abbreviation` varchar(10) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `rules` text,
  `contact_email` varchar(255) DEFAULT NULL,
  `out_of_season_surcharge` decimal(5,2) NOT NULL DEFAULT '10.00',
  `global_discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `show_discount_notification` tinyint(1) NOT NULL DEFAULT '1',
  `show_global_discount_notification` tinyint(1) NOT NULL DEFAULT '1',
  `terms_and_conditions` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_supplier_markets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `market_name` varchar(100) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'EUR',
  `currency_symbol` varchar(5) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__booking_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `message_id` int DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(2048) NOT NULL,
  `uploaded_by` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__booking_client_activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_request_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `screen_size` varchar(20) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_details` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_request_id` (`booking_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__booking_communication` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__booking_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_ref` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'New',
  `created_at` datetime DEFAULT NULL,
  `property_name` varchar(255) DEFAULT NULL,
  `accommodation_url` varchar(2048) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `adults` int DEFAULT NULL,
  `children` int DEFAULT NULL,
  `child_ages` varchar(255) DEFAULT NULL,
  `price_estimate` varchar(100) DEFAULT NULL,
  `unit_count` int DEFAULT '1',
  `discount_note` varchar(255) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_country` varchar(255) DEFAULT NULL,
  `client_message` text,
  `final_price` decimal(10,2) DEFAULT NULL,
  `payment_link` varchar(2048) DEFAULT NULL,
  `admin_notes` text,
  `pin` varchar(10) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `client_ip_address` varchar(45) DEFAULT NULL,
  `client_user_agent` text,
  `terms_log_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__booking_request_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `old_value` text,
  `new_value` text,
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__booking_supplier_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `old_value` text,
  `new_value` text,
  PRIMARY KEY (`id`),
  KEY `idx_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `#__bookingmanager_terms_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `booking_id` INT NOT NULL,
  `terms_content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
