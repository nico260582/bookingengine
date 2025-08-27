<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class BookingmanagerModelDiagnostic extends BaseDatabaseModel
{
    public function getMailSettings()
    {
        $config = Factory::getConfig();
        $settings = new \stdClass();
        $settings->mailer = $config->get('mailer');
        $settings->mailfrom = $config->get('mailfrom');
        $settings->fromname = $config->get('fromname');
        return $settings;
    }

    public function getSchemaHealthChecks()
    {
        $checks = [];
        $requiredSchema = [
            '#__bookingmanager_complexes' => ['id', 'name', 'alias', 'published'],
            '#__bookingmanager_complex_property_map' => ['property_id', 'complex_id', 'priority'],
            '#__bookingmanager_main_regions' => ['id', 'name', 'published'],
            '#__bookingmanager_properties' => ['id', 'article_id', 'max_guests', 'number_of_units', 'allow_extra_mattress', 'main_region_id', 'sub_region_id', 'published'],
            '#__bookingmanager_property_map' => ['property_id', 'supplier_id'],
            '#__bookingmanager_rates' => ['property_id', 'season_name', 'rates', 'active_markets', 'base_guest_number', 'override_admin_commission', 'admin_commission'],
            '#__bookingmanager_sub_regions' => ['id', 'main_region_id', 'name', 'published'],
            '#__bookingmanager_suppliers' => ['id', 'name', 'alias', 'abbreviation', 'published', 'rules', 'contact_email', 'out_of_season_surcharge', 'global_discount', 'show_discount_notification', 'show_global_discount_notification'],
            '#__bookingmanager_supplier_markets' => ['id', 'supplier_id', 'market_name', 'currency', 'currency_symbol', 'state'],
            '#__bookingmanager_templates' => ['id', 'title', 'type', 'subject', 'body'],
            '#__booking_attachments' => ['id', 'request_id', 'message_id', 'file_name', 'file_path', 'uploaded_by', 'created_at'],
            '#__booking_client_activity_logs' => ['id', 'booking_request_id', 'created_at', 'user_id', 'ip_address', 'user_agent', 'screen_size', 'action_type', 'action_details'],
            '#__booking_communication' => ['id', 'request_id', 'created_at', 'author', 'message'],
            '#__booking_requests' => ['id', 'booking_ref', 'status', 'created_at', 'property_name', 'accommodation_url', 'client_name', 'client_email', 'start_date', 'end_date', 'adults', 'children', 'child_ages', 'price_estimate', 'unit_count', 'discount_note', 'client_phone', 'client_country', 'client_message', 'final_price', 'payment_link', 'admin_notes', 'pin', 'user_id', 'client_ip_address', 'client_user_agent'],
            '#__booking_request_logs' => ['id', 'request_id', 'created_at', 'user_id', 'user_name', 'field_name', 'old_value', 'new_value'],
            '#__booking_supplier_logs' => ['id', 'supplier_id', 'created_at', 'user_id', 'user_name', 'field_name', 'old_value', 'new_value'],
        ];

        foreach ($requiredSchema as $tableName => $columns) {
            foreach ($columns as $columnName) {
                $check = new \stdClass();
                $check->name = "Column `{$columnName}` in `{$tableName}`";
                $check->status = $this->columnExists($tableName, $columnName);
                $checks[] = $check;
            }
        }

        return $checks;
    }

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
}