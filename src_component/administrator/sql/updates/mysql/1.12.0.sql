ALTER TABLE `#__booking_requests` ADD COLUMN `terms_agreed` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__booking_requests` ADD COLUMN `terms_agreed_at` DATETIME;
