CREATE TABLE `#__booking_supplier_communication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_request_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime NOT NULL,
  `sent_by_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_request_id` (`booking_request_id`),
  KEY `idx_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
