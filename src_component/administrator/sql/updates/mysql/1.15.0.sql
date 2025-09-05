CREATE TABLE IF NOT EXISTS `#__booking_supplier_communication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_request_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime NOT NULL,
  `sent_by_user_id` int(11) NOT NULL,
  `whatsapp_sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `booking_request_id` (`booking_request_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__bookingmanager_suppliers` ADD `contact_phone` VARCHAR(255) NULL DEFAULT NULL AFTER `contact_email`;
