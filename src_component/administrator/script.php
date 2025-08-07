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

    private function runInstallQueries($parent){
        $db = Factory::getDbo();
        
        $queries = array();
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_requests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `booking_ref` varchar(255) DEFAULT NULL,
          `status` varchar(50) DEFAULT 'New',
          `created_at` datetime DEFAULT NULL,
          `property_name` varchar(255) DEFAULT NULL,
          `accommodation_url` varchar(2048) DEFAULT NULL,
          `client_name` varchar(255) DEFAULT NULL,
          `client_email` varchar(255) DEFAULT NULL,
          `start_date` date DEFAULT NULL,
          `end_date` date DEFAULT NULL,
          `adults` int(11) DEFAULT NULL,
          `children` int(11) DEFAULT NULL,
          `child_ages` varchar(255) DEFAULT NULL,
          `price_estimate` varchar(100) DEFAULT NULL,
          `unit_count` int(11) DEFAULT 1,
          `discount_note` varchar(255) DEFAULT NULL,
          `client_phone` varchar(50) DEFAULT NULL,
          `client_country` varchar(255) DEFAULT NULL,
          `client_message` text,
          `final_price` decimal(10,2) DEFAULT NULL,
          `payment_link` varchar(2048) DEFAULT NULL,
          `admin_notes` text,
          `pin` varchar(10) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_communication` ( `id` int(11) NOT NULL AUTO_INCREMENT, `request_id` int(11) NOT NULL, `created_at` datetime DEFAULT NULL, `author` varchar(255) DEFAULT NULL, `message` text, PRIMARY KEY (`id`), KEY `idx_request_id` (`request_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_attachments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `request_id` int(11) NOT NULL,
          `file_name` varchar(255) NOT NULL,
          `file_path` varchar(2048) NOT NULL,
          `uploaded_by` varchar(255) NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_request_id` (`request_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_request_logs` ( `id` int(11) NOT NULL AUTO_INCREMENT, `request_id` int(11) NOT NULL, `created_at` datetime NOT NULL, `user_id` int(11) NOT NULL, `user_name` varchar(255) NOT NULL, `field_name` varchar(255) NOT NULL, `old_value` text, `new_value` text, PRIMARY KEY (`id`), KEY `idx_request_id` (`request_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__booking_supplier_logs` ( `id` int(11) NOT NULL AUTO_INCREMENT, `supplier_id` int(11) NOT NULL, `created_at` datetime NOT NULL, `user_id` int(11) NOT NULL,
        `user_name` varchar(255) NOT NULL, `field_name` varchar(255) NOT NULL, `old_value` text, `new_value` text, PRIMARY KEY (`id`), KEY `idx_supplier_id` (`supplier_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_suppliers` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `alias` varchar(255) NOT NULL, `abbreviation` varchar(10) NOT NULL, `published` tinyint(1) NOT NULL DEFAULT '1', `rules` text, `contact_email` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_property_map` ( `property_id` int(11) NOT NULL, `supplier_id` int(11) NOT NULL, PRIMARY KEY (`property_id`), KEY `idx_supplier_id` (`supplier_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_rates` ( `property_id` int(11) NOT NULL, `season_name` varchar(255) NOT NULL, `base_rate` decimal(10,2) NOT NULL, PRIMARY KEY (`property_id`, `season_name`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $queries[] = "CREATE TABLE IF NOT EXISTS `#__bookingmanager_templates` (`id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `type` varchar(50) NOT NULL, `subject` varchar(255) DEFAULT NULL, `body` text, PRIMARY KEY (`id`), UNIQUE KEY `idx_type` (`type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        foreach ($queries as $query) {
            $db->setQuery($query);
            try { $db->execute(); } catch (Exception $e) {}
        }

        // Add columns for commission override if they don't exist
        if (!$this->columnExists('#__bookingmanager_rates', 'override_admin_commission')) {
            $db->setQuery("ALTER TABLE `#__bookingmanager_rates` ADD COLUMN `override_admin_commission` TINYINT(1) NOT NULL DEFAULT 0");
            $db->execute();
            JFactory::getApplication()->enqueueMessage('Table #__bookingmanager_rates updated with override_admin_commission column.', 'message');
        }

        if (!$this->columnExists('#__bookingmanager_rates', 'admin_commission')) {
            $db->setQuery("ALTER TABLE `#__bookingmanager_rates` ADD COLUMN `admin_commission` DECIMAL(5,2) DEFAULT NULL");
            $db->execute();
            JFactory::getApplication()->enqueueMessage('Table #__bookingmanager_rates updated with admin_commission column.', 'message');
        }

        // Make base_rate nullable
        // Note: We don't check if this is already nullable, as MODIFY COLUMN is idempotent for this purpose.
        // A more complex check would be needed to inspect the column's properties if we wanted to avoid running this every time.
        $db->setQuery("ALTER TABLE `#__bookingmanager_rates` MODIFY COLUMN `base_rate` DECIMAL(10,2) NULL");
        try { $db->execute(); } catch (Exception $e) {}

        // Add user_id to booking_requests table
        if (!$this->columnExists('#__booking_requests', 'user_id')) {
            $db->setQuery("ALTER TABLE `#__booking_requests` ADD COLUMN `user_id` INT(11) NULL DEFAULT NULL, ADD INDEX `idx_user_id` (`user_id`)");
            $db->execute();
            JFactory::getApplication()->enqueueMessage('Table #__booking_requests updated with user_id column.', 'message');
        }
        
        $this->addSampleData($db);
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
                'body'    => "<p>Hello [client_name],</p>\n                            <p>Thank you for your booking request (Ref: <strong>[booking_ref]</strong>) via Book Holidays Mauritius, powered by RT Holidays Ltd.</p>\n                            <hr>\n                            <p>🛎️ <strong>Property:</strong> [property_name]<br>\n                               📅 <strong>Dates:</strong> [start_date_formatted] to [end_date_formatted] ([nights] nights)<br>\n                               👨‍👩‍👧 <strong>Guests:</strong> [guest_details]<br>\n                               💰 <strong>Est. Price:</strong> [price_estimate]<br>\n                               💬 <strong>Message received:</strong> [client_message]<br>\n                               [discount_note]\n                            </p>\n                            <p>You can view and manage your request by visiting our client portal. Please have your PIN ready.</p>\n                            <p><a href=\"[client_portal_link]\">Access Your Booking Request</a> (PIN: <strong>[pin]</strong>)</p>\n                            <p><em>View Property: <a href=\"[accommodation_url]\">[accommodation_url]</a></em></p>\n                            <hr>\n                            <p>We are pleased to assist you here. If you have any questions, feel free to let us know!</p>\n                            <p>Warm regards,<br>The RT Holidays Team</p>\n                            <p>[whatsapp_link_client]</p>"
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
        $queries = array("DROP TABLE IF EXISTS `#__booking_communication`;", "DROP TABLE IF EXISTS `#__booking_requests`;", "DROP TABLE IF EXISTS `#__booking_request_logs`;", "DROP TABLE IF EXISTS `#__booking_supplier_logs`;", "DROP TABLE IF EXISTS `#__bookingmanager_suppliers`;", "DROP TABLE IF EXISTS `#__bookingmanager_property_map`;", "DROP TABLE IF EXISTS `#__bookingmanager_rates`;", "DROP TABLE IF EXISTS `#__bookingmanager_templates`;", "DROP TABLE IF EXISTS `#__booking_attachments`;");
        foreach ($queries as $query) { $db->setQuery($query); try { $db->execute(); } catch (Exception $e) {} }
    }
}