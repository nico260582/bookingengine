<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
class com_bookingmanagerInstallerScript
{
    private function columnExists($tableName, $columnName)
    {
        $db = Factory::getDbo();
        $tableName = $db->replacePrefix($tableName);

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME = ' . $db->quote($tableName))
            ->where('COLUMN_NAME = ' . $db->quote($columnName));

        $db->setQuery($query);
        return (bool) $db->loadResult();
    }

    public function install($parent) { $this->runInstallQueries($parent); return true; }
    public function uninstall($parent) { $this->runUninstallQueries($parent); return true; }
    public function update($parent) { $this->runInstallQueries($parent); return true; }

    private function runInstallQueries($parent)
    {
        $db = Factory::getDbo();

        $queries = array();

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_complexes` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `alias` varchar(255) NOT NULL,
          `published` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_complex_property_map` (
          `property_id` int NOT NULL,
          `complex_id` int NOT NULL,
          `priority` int NOT NULL DEFAULT '0',
          PRIMARY KEY (`property_id`,`complex_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_main_regions` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `published` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_properties` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_property_map` (
          `property_id` int NOT NULL,
          `supplier_id` int NOT NULL,
          PRIMARY KEY (`property_id`),
          KEY `idx_supplier_id` (`supplier_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_rates` (
          `property_id` int NOT NULL,
          `season_name` varchar(255) NOT NULL,
          `rates` text,
          `active_markets` text,
          `base_guest_number` int DEFAULT NULL,
          `override_admin_commission` tinyint(1) NOT NULL DEFAULT '0',
          `admin_commission` decimal(5,2) DEFAULT NULL,
          PRIMARY KEY (`property_id`,`season_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_sub_regions` (
          `id` int NOT NULL AUTO_INCREMENT,
          `main_region_id` int NOT NULL,
          `name` varchar(255) NOT NULL,
          `published` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`),
          KEY `idx_main_region_id` (`main_region_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_suppliers` (
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
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_clients` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `pin` varchar(255) NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_supplier_markets` (
          `id` int NOT NULL AUTO_INCREMENT,
          `supplier_id` int NOT NULL,
          `market_name` varchar(100) NOT NULL,
          `currency` varchar(10) NOT NULL DEFAULT 'EUR',
          `currency_symbol` varchar(5) DEFAULT NULL,
          `state` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`),
          KEY `idx_supplier_id` (`supplier_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_templates` (
          `id` int NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `type` varchar(50) NOT NULL,
          `subject` varchar(255) DEFAULT NULL,
          `body` text,
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_attachments` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_client_activity_logs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_communication` (
          `id` int NOT NULL AUTO_INCREMENT,
          `request_id` int NOT NULL,
          `created_at` datetime DEFAULT NULL,
          `author` varchar(255) DEFAULT NULL,
          `message` text,
          PRIMARY KEY (`id`),
          KEY `idx_request_id` (`request_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_requests` (
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
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_request_logs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_supplier_logs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

        foreach ($queries as $query) {
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                // Log or handle error if necessary
            }
        }

        // Add new columns and tables for T&C feature
        if (!$this->columnExists('#__bookingmanager_suppliers', 'terms_and_conditions')) {
            $db->setQuery("ALTER TABLE `#__bookingmanager_suppliers` ADD `terms_and_conditions` TEXT;");
            try { $db->execute(); } catch (Exception $e) {}
        }
        if (!$this->columnExists('#__booking_requests', 'terms_log_id')) {
            $db->setQuery("ALTER TABLE `#__booking_requests` ADD `terms_log_id` INT(11) NULL DEFAULT NULL;");
            try { $db->execute(); } catch (Exception $e) {}
        }
        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__bookingmanager_terms_log` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `booking_id` INT NOT NULL,
          `terms_content` TEXT NOT NULL,
          `created_at` DATETIME NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_booking_id` (`booking_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");
        try { $db->execute(); } catch (Exception $e) {}


        $this->addDefaultTemplates($db);
    }

    private function addSampleData($db) {
        $db->setQuery("SELECT COUNT(id) FROM `#__bookingmanager_suppliers`");
        if ($db->loadResult() == 0) {
            $rules_with_discount = '{"pricing_model":"SupplementPerGuest","country_discounts":[{"country":"Mauritius","discount_percent":"10","note":"10% Mauritian Resident discount applied!"}]}';
            $sample_suppliers = [
                "(1, 'Villa Rentals Ltd', 'villa-rentals-ltd', 'VRL', 1, " . $db->quote($rules_with_discount) . ", 'supplier1@example.com')",
                "(2, 'Beachfront Apartments Inc', 'beachfront-apartments-inc', 'BAI', 1, '{\"pricing_model\":\"FlatUnitRate\"}', 'supplier2@example.com')"
            ];
            $db->setQuery("INSERT INTO `#__bookingmanager_suppliers` (`id`, `name`, `alias`, `abbreviation`, `published`, `rules`, `contact_email`) VALUES " . implode(', ', $sample_suppliers));
            try { $db->execute(); } catch (Exception $e) {}
        }

        $db->setQuery("SELECT COUNT(id) FROM `#__booking_requests`");
        if ($db->loadResult() == 0) {
            $sample_requests = [
                "(1, 'BHM-010825-ABCD', 'New', NOW(), 'Beach Villa Paradise', 'https://example.com', 'John Doe', 'john.doe@example.com', '2025-12-20', '2025-12-27', 2, 1, '5', '€2100', 1, '', '+123456789', 'United States', 'Looking forward to our stay!', 'A1B2C3')",
                "(2, 'BHM-020825-EFGH', 'Confirmed', NOW(), 'Sunset Apartment', 'https://example.com', 'Jane Smith', 'jane.smith@example.com', '2025-11-15', '2025-11-22', 2, 0, '', '€800', 1, '', '+987654321', 'Germany', 'Requesting an early check-in if possible.', 'D4E5F6')"
            ];
            $db->setQuery("INSERT INTO `#__booking_requests` (`id`, `booking_ref`, `status`, `created_at`, `property_name`, `accommodation_url`, `client_name`, `client_email`, `start_date`, `end_date`, `adults`, `children`, `child_ages`, `price_estimate`, `unit_count`, `discount_note`, `client_phone`, `client_country`, `client_message`, `pin`) VALUES " . implode(', ', $sample_requests));
            try { $db->execute(); } catch (Exception $e) {}
        }
    }

    private function addDefaultTemplates($db) {
        $templates = [
            [
                'title'   => 'Admin - New Booking Notification',
                'type'    => 'email_admin_notify',
                'subject' => 'New Booking Request: [booking_ref] for [property_name]',
                'body'    => "<h3>Booking Request Details</h3>\n                            <ul>\n                                <li><strong>Reference:</strong> [booking_ref]</li>\n                                <li><strong>Accommodation:</strong> [property_name]</li>\n                                <li><strong>Name:</strong> [client_name]</li>\n                                <li><strong>Email:</strong> [client_email]</li>\n                                <li><strong>Telephone:</strong> [client_phone]</li>\n                                <li><strong>Country:</strong> [client_country]</li>\n                                <li><strong>Dates:</strong> [start_date_formatted] to [end_date_formatted] ([nights] nights)</li>\n                                <li><strong>Guests:</strong> [guest_details]</li>\n                                <li><strong>Units:</strong> [unit_count]</li>\n                                <li><strong>Estimated Price:</strong> [price_estimate]</li>\n                                <li><strong>Note:</strong> [discount_note]</li>\n                            </ul>\n                            <p><strong>Client's Message:</strong><br>[client_message]</p>\n                            <hr>\n                            <h3>Quick Actions</h3>\n                            <p>[whatsapp_link_admin]</p>"
            ],
            [
                'title'   => 'Client - Booking Confirmation',
                'type'    => 'email_client_confirm',
                'subject' => 'Thank you for your booking request (Ref: [booking_ref])',
                'body'    => "<p>Hello [client_name],</p>\n                            <p>Thank you for your booking request (Ref: <strong>[booking_ref]</strong>) via Book Holidays Mauritius, powered by RT Holidays Ltd.</p>\n                            <hr>\n                            <p>🛎️ <strong>Property:</strong> [property_name]<br>\n                               📅 <strong>Dates:</strong> [start_date_formatted] to [end_date_formatted] ([nights] nights)<br>\n                               👨‍👩‍👧 <strong>Guests:</strong> [guest_details]<br>\n                               💰 <strong>Est. Price:</strong> [price_estimate]<br>\n                               💬 <strong>Message received:</strong> [client_message]<br>\n                               [discount_note]\n                            </p>\n                            <p>You can view and manage your request by visiting our client portal. Please have your PIN ready.</p>\n                            <p><a href=\"[client_portal_link]\">Access Your Booking Request</a> (PIN: <strong>[pin]</strong>)</p>\n                            <p><em>View Property: <a href=\"[accommodation_url]\">[accommodation_url]</a></em></p>\n                            [terms_and_conditions_link]\n                            <hr>\n                            <p>We are pleased to assist you here. If you have any questions, feel free to let us know!</p>\n                            <p>Warm regards,<br>The RT Holidays Team</p>\n                            <p>[whatsapp_link_client]</p>"
            ],
            [
                'title'   => 'Admin - Client Reply Notification',
                'type'    => 'email_admin_client_reply',
                'subject' => 'New Client Message on Booking Request [booking_ref]',
                'body'    => "<h3>New Client Message</h3><p>A new message has been received from [client_name] regarding booking request [booking_ref] for [property_name].</p><p><strong>Message:</strong></p><blockquote>[client_message]</blockquote><p>Please log in to the administrator area to reply.</p>"
            ],
            [
                'title'   => 'Client - Admin Reply Notification',
                'type'    => 'email_client_admin_reply',
                'subject' => 'You have a new message regarding your booking request [booking_ref]',
                'body'    => "<p>Hello [client_name],</p><p>You have received a new message from our team regarding your booking request [booking_ref].</p><p><strong>Message:</strong></p><blockquote>[admin_message]</blockquote><hr><p>To reply to this message, please use our secure client portal:</p><p><a href=\"[client_portal_link]\">Reply via Client Portal</a> (PIN: <strong>[pin]</strong>)</p><p>Warm regards,<br>The Book Holidays Mauritius Team</p>"
            ],
            [
                'title'   => 'Supplier - Availability Request',
                'type'    => 'email_supplier_availability',
                'subject' => 'Availability Request for [property_name] (Ref: [booking_ref])',
                'body'    => "<p>Dear Supplier,</p><p>Please confirm availability for the following request:</p><ul><li><strong>Property:</strong> [property_name]</li><li><strong>Dates:</strong> [start_date] to [end_date] ([nights] nights)</li><li><strong>Guests:</strong> [guest_details]</li></ul><p>Thank you.</p>"
            ],
            [
                'title'   => 'WhatsApp - Client Reply (to Admin)',
                'type'    => 'whatsapp_client_reply',
                'subject' => null,
                'body'    => "Hello,\nMy Booking Request is Ref: [booking_ref]\n🏡 Accommodation: [property_name]\n👤 Name: [client_name]\n📅 Dates: [start_date] to [end_date]\n👨‍👩‍👧 Guests: [guest_details]\n💬 Your Message: [client_message]\n\n🔗 View Property: [accommodation_url]"
            ],
            [
                'title'   => 'WhatsApp - Admin Reply (to Client)',
                'type'    => 'whatsapp_admin_reply',
                'subject' => null,
                'body'    => "Hello [client_name],\n\nThis is a message regarding your booking request [booking_ref] for [property_name]."
            ],
            [
                'title'   => 'Client - New User Account Details',
                'type'    => 'email_client_new_user',
                'subject' => 'Your New Account on Book Holidays Mauritius',
                'body'    => "<p>Hello [client_name],</p>\n                            <p>An account has been created for you on Book Holidays Mauritius. You can use these details to log in and manage your bookings.</p>\n                            <hr>\n                            <p><strong>Username:</strong> [username]<br>\n                               <strong>Password:</strong> [password]<br>\n                            </p>\n                            <p>We strongly recommend that you change your password after logging in for the first time.</p>\n                            <p><a href=\"[login_link]\">Click here to log in</a></p>\n                            <hr>\n                            <p>Warm regards,<br>The Book Holidays Mauritius Team</p>"
            ],
            [
                'title'   => 'Admin - Booking Modification',
                'type'    => 'email_admin_booking_modified',
                'subject' => 'Booking [booking_ref] has been modified by the client',
                'body'    => "<h3>Booking Request [booking_ref] has been modified by the client.</h3>\n                            <p>Property: [property_name]</p>\n                            <h4>Changes:</h4>\n                            <table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" style=\"width:100%; border-collapse: collapse;\">\n                                <thead>\n                                    <tr style=\"background-color:#f2f2f2;\">\n                                        <th style=\"padding: 8px; border: 1px solid #ddd; text-align: left;\">Field</th>\n                                        <th style=\"padding: 8px; border: 1px solid #ddd; text-align: left;\">Old Value</th>\n                                        <th style=\"padding: 8px; border: 1px solid #ddd; text-align: left;\">New Value</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    [changes_table]\n                                </tbody>\n                            </table>\n                            <hr>\n                            <h4>Full Booking Details:</h4>\n                            [booking_details_html]\n                            <p>You can view the booking by visiting the administrator area.</p>"
            ]
        ];

        foreach ($templates as $template) {
            $db->setQuery("SELECT id FROM `#__bookingmanager_templates` WHERE `type` = " . $db->quote($template['type']));
            if (!$db->loadResult()) {
                $db->setQuery("INSERT INTO `#__bookingmanager_templates` (`title`, `type`, `subject`, `body`) VALUES (" . $db->quote($template['title']) . ", " . $db->quote($template['type']) . ", " . $db->quote($template['subject']) . ", " . $db->quote($template['body']) . ")");
                try { $db->execute(); } catch (Exception $e) {}
            }
        }
    }

    private function runUninstallQueries($parent) {
        $db = Factory::getDbo();
        $queries = array(
            "DROP TABLE IF EXISTS `#__booking_communication`;",
            "DROP TABLE IF EXISTS `#__booking_requests`;",
            "DROP TABLE IF EXISTS `#__booking_request_logs`;",
            "DROP TABLE IF EXISTS `#__booking_supplier_logs`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_suppliers`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_property_map`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_rates`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_templates`;",
            "DROP TABLE IF EXISTS `#__booking_attachments`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_complexes`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_complex_property_map`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_main_regions`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_properties`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_sub_regions`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_supplier_markets`;",
            "DROP TABLE IF EXISTS `#__booking_client_activity_logs`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_clients`;",
            "DROP TABLE IF EXISTS `#__bookingmanager_terms_log`;"
        );
        foreach ($queries as $query) { $db->setQuery($query); try { $db->execute(); } catch (Exception $e) {} }
    }
}
